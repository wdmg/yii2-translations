<?php

namespace wdmg\translations\controllers;

use function GuzzleHttp\Psr7\parse_header;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use wdmg\translations\models\Sources;
use wdmg\translations\models\Translations;
use wdmg\translations\models\Languages;
use wdmg\translations\models\TranslationsSearch;
use wdmg\translations\models\TranslationsEdit;

/**
 * ListController implements the CRUD actions for Translations model.
 */
class ListController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $defaultAction = 'index';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];

        // If auth manager not configured use default access control
        if(!Yii::$app->authManager) {
            $behaviors['access'] = [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true
                    ],
                ]
            ];
        }

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $viewed = array();
        $session = Yii::$app->session;

        if(isset($session['viewed-flash']) && is_array($session['viewed-flash']))
            $viewed = $session['viewed-flash'];

        if (Languages::getCount() == 0 && !in_array('translations-disabled-no-languages', $viewed) && is_array($viewed)) {
            Yii::$app->getSession()->setFlash(
                'warning',
                Yii::t(
                    'app/modules/translations',
                    'There are currently no active (available) languages. Therefore, the module cannot be activated by the system.'
                )
            );
            $session['viewed-flash'] = array_merge(array_unique($viewed), ['translations-disabled-no-languages']);
        }

        if (Translations::getCount() == 0 && !in_array('translations-disabled-because-empty', $viewed) && is_array($viewed)) {
            Yii::$app->getSession()->setFlash(
                'warning',
                Yii::t(
                    'app/modules/translations',
                    'There are currently no active (available) translations. Therefore, the module cannot be activated by the system.'
                )
            );
            $session['viewed-flash'] = array_merge(array_unique($viewed), ['translations-disabled-because-empty']);
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all Translations models.
     * @return mixed
     */
    public function actionIndex()
    {
        $languages = new Languages();
        $searchModel = new TranslationsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'languages' => $languages,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Scaning and add new or update Translations models.
     * @return mixed
     */
    public function actionScan()
    {
        $module = $this->module;
        $langList = null;

        $languagesModel = new Languages();
        foreach ($languagesModel->find()->where(['status' => 1])->select('locale')->asArray()->groupBy('locale')->all() as $locale) {
            foreach ($locale as $lang) {
                $langList[] = $lang;
            }
        }

        if (empty($langList)) {
            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/translations',
                    'Error! It is not possible to scan and add translations without installed system languages.'
                )
            );
        } else {

            // @TODO: Split this stuff to standalone method

            // Get available translations
            $translationsList = [];
            foreach ($langList as $lang) {
                $translationsList = ArrayHelper::merge($translationsList, $module->scanTranslations([$lang]));
            }

            // Get source of translations
            $sourcesList = $module->getSourceMessages($translationsList);

            $sourceCount = 0;
            if (!is_null($sourcesList)) {
                $sourcesIds = [];
                $insertRows = [];
                foreach ($sourcesList as $lang => $sources) {
                    foreach ($sources as $category => $messages) {
                        foreach ($messages as $message) {
                            $sourcesModel = new Sources();
                            $sourcesModel->language = $lang;
                            $sourcesModel->category = $category;
                            $sourcesModel->message = $message;
                            $sourcesModel->alias = $sourcesModel->getStringAlias($message); // @TODO: Issue, where alias key must be unique.
                            $sourcesModel->created_at = new yii\db\Expression('NOW()');
                            $sourcesModel->created_by = Yii::$app->getUser()->id;
                            $sourcesModel->updated_at = new yii\db\Expression('NOW()');
                            $sourcesModel->updated_by = Yii::$app->getUser()->id;

                            if ($sourcesModel->validate()) {
                                $insertRows[] = $sourcesModel;
                                $sourceCount++;
                                $sourcesIds[$category][$message] = $sourceCount;
                            } else {
                                Yii::$app->getSession()->setFlash(
                                    'warning',
                                    Yii::t(
                                        'app/modules/translations',
                                        'An error occurred while added/updated the sources: `{errors}`.',
                                        [
                                            'errors' => var_export($sourcesModel->errors, true)
                                        ]
                                    )
                                );
                            }

                        }
                    }
                }

                $sourcesModel = new Sources();
                Yii::$app->db->createCommand()->batchInsert(Sources::tableName(), $sourcesModel->attributes(), $insertRows)->execute();
            }

            $translationsCount = 0;
            if (!is_null($translationsList)) {
                $insertRows = [];
                foreach ($translationsList as $lang => $sources) {
                    foreach ($sources as $category => $translations) {
                        foreach ($translations as $key => $translation) {

                            if (isset($sourcesIds[$category][$key])) {
                                $id = $sourcesIds[$category][$key];
                                $translationsModel = new Translations();
                                $translationsModel->id = $id;
                                $translationsModel->language = $lang;
                                $translationsModel->translation = $translation;
                                $translationsModel->status = 1;
                                $translationsModel->created_at = new yii\db\Expression('NOW()');
                                $translationsModel->created_by = Yii::$app->getUser()->id;
                                $translationsModel->updated_at = new yii\db\Expression('NOW()');
                                $translationsModel->updated_by = Yii::$app->getUser()->id;

                                if ($translationsModel->validate()) {
                                    $insertRows[] = $translationsModel;
                                    $translationsCount++;
                                } else {
                                    Yii::$app->getSession()->setFlash(
                                        'warning',
                                        Yii::t(
                                            'app/modules/translations',
                                            'An error occurred while added/updated the translation: `{errors}`.',
                                            [
                                                'errors' => var_export($translationsModel->errors, true)
                                            ]
                                        )
                                    );
                                }
                            }

                        }
                    }
                }

                $translationsModel = new Translations();
                Yii::$app->db->createCommand()->batchInsert(Translations::tableName(), $translationsModel->attributes(), $insertRows)->execute();
            }

            if ($sourceCount > 0 && $translationsCount > 0) {
                Yii::$app->getSession()->setFlash(
                    'success',
                    Yii::t(
                        'app/modules/translations',
                        'OK! {sources} source and {translations} translations have been added/updated.',
                        [
                            'sources' => $sourceCount,
                            'translations' => $translationsCount
                        ]
                    )
                );
            }

        }





        return $this->run('list/index');
    }

    /**
     * Deleting all Translations models.
     * @return mixed
     */
    public function actionClear()
    {
        try {
            Yii::$app->db->createCommand()->checkIntegrity(false, '', Translations::tableName())->execute();
            Yii::$app->db->createCommand()->truncateTable(Translations::tableName())->execute();
            Yii::$app->db->createCommand()->checkIntegrity(true, '', Translations::tableName())->execute();

            Yii::$app->db->createCommand()->checkIntegrity(false, '', Sources::tableName())->execute();
            Yii::$app->db->createCommand()->truncateTable(Sources::tableName())->execute();
            Yii::$app->db->createCommand()->checkIntegrity(true, '', Sources::tableName())->execute();

            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t(
                    'app/modules/translations',
                    'OK! All translations and sources has been successfully deleted.'
                )
            );
        } catch (\yii\db\Exception $exception)  {
            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/translations',
                    'An error occurred while deleting translations and sources.'
                )
            );
        }
        return $this->run('list/index');
    }

    /**
     * Create new Translations models.
     * @return mixed
     */
    public function actionCreate()
    {
        $sources = new Sources();
        $translations = new TranslationsEdit();
        //$translations->scenario = "create";

        if (\Yii::$app->request->isAjax && $translations->load(\Yii::$app->request->post())){
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $alias = $translations->alias;
            if (!is_null($translations->message))
                $alias = $sources->getStringAlias($translations->message);

            return ArrayHelper::merge(ActiveForm::validate($translations), ['alias' => $alias]);

        }

        if (Yii::$app->request->isPost) {
            if ($translations->load(Yii::$app->request->post())) {

                $sources->language = $translations->language;
                $sources->category = $translations->category;
                $sources->message = $translations->message;
                $sources->alias = $translations->alias;

                $valid = $sources->validate();
                $valid = $translations->validate() && $valid;

                if ($valid) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if($sources->save(false)) {
                            $translations->id = $sources->id;
                            if ($translations->save(false)) {
                                Yii::$app->getSession()->setFlash(
                                    'success',
                                    Yii::t(
                                        'app/modules/translations',
                                        'OK! Translation successfully added.'
                                    )
                                );
                            }
                        } else {
                            Yii::$app->getSession()->setFlash(
                                'danger',
                                Yii::t(
                                    'app/modules/translations',
                                    'An error occurred while added a translation.'
                                )
                            );
                        }
                        $transaction->commit();
                    } catch( Exception $e ) {
                        $transaction->rollback();
                        throw $e;
                    }
                    return $this->run('list/index');
                }
            }
        }

        return $this->render('create', [
            'model' => $translations,
            'languagesList' => Translations::getLanguagesList(false),
            'statusModes' => Translations::getStatusModeList(false)
        ]);
    }


    /**
     * Update new Translations models.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $sources = new Sources();
        $editModel = new TranslationsEdit();
        $translations = $editModel->search(['id' => intval($id)]);
        //$translations->scenario = "update";

        if (\Yii::$app->request->isAjax && $translations->load(\Yii::$app->request->post())){
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $alias = $translations->alias;
            if (!is_null($translations->message))
                $alias = $sources->getStringAlias($translations->message);

            return ArrayHelper::merge(ActiveForm::validate($translations), ['alias' => $alias]);

        }

        if (Yii::$app->request->isPost) {
            if ($translations->load(Yii::$app->request->post())) {

                if (is_null($sources->id))
                    $sources = $sources::findOne(intval($id));

                $sources->language = $translations->language;
                $sources->category = $translations->category;
                $sources->message = $translations->message;
                $sources->alias = $translations->alias;

                $valid = $sources->validate();
                $valid = $translations->validate() && $valid;

                if ($valid) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if($sources->update(false)) {
                            $translations->id = $sources->id;
                            if ($translations->update(false)) {
                                Yii::$app->getSession()->setFlash(
                                    'success',
                                    Yii::t(
                                        'app/modules/translations',
                                        'OK! Translation successfully updated.'
                                    )
                                );
                            }
                        } else {
                            Yii::$app->getSession()->setFlash(
                                'danger',
                                Yii::t(
                                    'app/modules/translations',
                                    'An error occurred while updating a translation.'
                                )
                            );
                        }
                        $transaction->commit();
                    } catch( Exception $e ) {
                        $transaction->rollback();
                        throw $e;
                    }
                    return $this->run('list/index');
                }
            }
        }

        if ($translations !== null && $sources !== null) {
            return $this->render('update', [
                'model' => $translations,
                'languagesList' => Translations::getLanguagesList(false),
                'statusModes' => Translations::getStatusModeList(false)
            ]);
        }

        throw new NotFoundHttpException(Yii::t('app/modules/translations', 'The requested translation does not exist.'));
    }

    /**
     * View a Translations models.
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->renderAjax('view', [
            'model' => $model
        ]);
    }

    /**
     * Once delete Translation model.
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t(
                    'app/modules/translations',
                    'OK! Translation has been successfully deleted.'
                )
            );
        } else {
            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/translations',
                    'An error occurred while deleting translation.'
                )
            );
        }
        return $this->run('list/index');
    }

    /**
     * Finds the Translation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Translations::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/translations', 'The requested translation does not exist.'));
    }

}
