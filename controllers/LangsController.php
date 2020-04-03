<?php

namespace wdmg\translations\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use \yii\helpers\ArrayHelper;
use wdmg\translations\models\Languages;
use wdmg\translations\models\Translations;
use wdmg\translations\models\LanguagesSearch;

/**
 * LangsController implements the CRUD actions for Langs model.
 */
class LangsController extends Controller
{
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
     * Lists all Langs models.
     * @return mixed
     */
    public function actionIndex()
    {
        $locales = [];
        $module = $this->module;
        $model = new Languages();
        $searchModel = new LanguagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Change model status (aJax request by switcher)
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->get('change') == "status") {
                if ($id = Yii::$app->request->post('id', null)) {
                    $status = Yii::$app->request->post('value', 0);
                    if ($model = $model->findOne(['id' => intval($id)])) {
                        if (intval($model->is_default) == 0 || intval($model->is_system) == 0) {
                            if ($model->updateAttributes(['status' => intval($status)]))
                                return true;
                            else
                                return false;
                        }
                    } else {
                        return false;
                    }
                }
            } elseif (Yii::$app->request->get('change') == "is_frontend") {
                if ($id = Yii::$app->request->post('id', null)) {
                    $is_frontend = Yii::$app->request->post('value', 0);
                    if ($model = $model->findOne(['id' => intval($id)])) {
                        if ($model->updateAttributes(['is_frontend' => intval($is_frontend)]))
                            return true;
                        else
                            return false;
                    } else {
                        return false;
                    }
                }
            }
        } elseif (Yii::$app->request->isPost) {
            if ($post = Yii::$app->request->post()) {

                $activate = false;
                if (isset($post['Languages']['autoActivate']))
                    $activate = $post['Languages']['autoActivate'];

                if ($locale = $module->parseLocale($post['Languages']["languages"], Yii::$app->language)) {
                    $model->setAttribute('url', $locale["short"]);
                    $model->setAttribute('locale', $locale["locale"]);
                    $model->setAttribute('name', $locale["name"]);
                    $model->setAttribute('is_default', $locale["is_default"]);
                    $model->setAttribute('is_system', 0);
                    $model->setAttribute('status', ($activate) ? $model::LANGUAGE_STATUS_ACTIVE : $model::LANGUAGE_STATUS_DISABLED);

                    if ($model->validate()) {
                        if($model->save()) {
                            Yii::$app->getSession()->setFlash(
                                'success',
                                Yii::t(
                                    'app/modules/translations',
                                    'OK! Language `{name}` successfully {status}.',
                                    [
                                        'name' => $model->name,
                                        'status' => ($activate) ? Yii::t('app/modules/translations', 'added and activated') : Yii::t('app/modules/translations', 'added')
                                    ]
                                )
                            );
                        } else {
                            Yii::$app->getSession()->setFlash(
                                'danger',
                                Yii::t(
                                    'app/modules/translations',
                                    'An error occurred while adding a language `{name}`.',
                                    [
                                        'name' => $model->name
                                    ]
                                )
                            );
                        }
                    }
                }
            }
        } else {
            if (Yii::$app->request->get('action') == "delete") {
                if (Yii::$app->request->get('id', null)) {
                    $id = Yii::$app->request->get('id');
                    if ($model = $model->findOne(['id' => intval($id)])) {
                        if (intval($model->is_default) == 0 || intval($model->is_system) == 0) {
                            if ($model->delete()) {
                                Yii::$app->getSession()->setFlash(
                                    'success',
                                    Yii::t(
                                        'app/modules/translations',
                                        'OK! Language `{name}` successfully deleted.',
                                        [
                                            'name' => $model->name,
                                        ]
                                    )
                                );
                            } else {
                                Yii::$app->getSession()->setFlash(
                                    'danger',
                                    Yii::t(
                                        'app/modules/translations',
                                        'An error occurred while deleting a language `{name}`.',
                                        [
                                            'name' => $model->name,
                                        ]
                                    )
                                );
                            }
                        }
                    } else {
                        throw new NotFoundHttpException(Yii::t('app/modules/translations', 'The requested language does not exist.'));
                    }
                }
            }
        }

        // Get the list of supported locales without already installed
        $languages = $model::getAllLanguages(false, false, true);
        $supported = ArrayHelper::map($languages, 'id', 'locale');

        foreach ($module->getLocales() as $data) {
            if (is_object($data)) {
                if (isset($data->locale)) {
                    if (!in_array($data->locale, $supported))
                        $locales[] = $data;
                }
            } elseif (is_array($data)) {
                if (isset($data['locale'])) {
                    if (!in_array($data['locale'], $supported))
                        $locales[] = $data;
                }
            }
        }

        return $this->render('index', [
            'model' => $model,
            'module' => $module,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'locales' => $locales
        ]);
    }

}
