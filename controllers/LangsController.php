<?php

namespace wdmg\translations\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use \yii\helpers\ArrayHelper;
use wdmg\translations\models\Languages;
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
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
                'class' => AccessControl::className(),
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

        if (Yii::$app->request->isPost) {
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
        }

        // Get the list of supported locales without already installed
        $languages = $model::getAllLanguages(false);
        foreach ($module->getLocales() as $data) {
            if (!in_array($data['locale'], array_keys($languages)))
                $locales[] = $data;
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
