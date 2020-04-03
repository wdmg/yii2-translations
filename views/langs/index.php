<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use wdmg\translations\FlagsAsset;
use wdmg\widgets\SelectInput;
/* @var $this yii\web\View */

$this->title = Yii::t('app/modules/translations', 'Languages list');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['list/index']];
$this->params['breadcrumbs'][] = $this->title;

$bundle = FlagsAsset::register($this);

$languages = [];
if (is_array($locales)) {
    foreach ($locales as $locale) {

        if (!($country = $locale['domain']))
            $country = '_unknown';

        $flag = Html::img($bundle->baseUrl . '/flags-iso/flat/24/'.$country.'.png');
        $languages[] = [$locale['locale'] => ($flag . '&nbsp;' . $locale['full']['current'] . ' <span class="text-muted pull-right">'.$locale['locale'].'</span>')];
    }
}

?>

<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="translations-langs-index">
    <?php Pjax::begin([
        'id' => "translationsLangsAjax",
        'timeout' => 5000
    ]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'url',
            'locale',

            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($data) use ($module, $bundle) {
                    $locale = $module->parseLocale($data->locale, Yii::$app->language);
                    if (!($country = $locale['domain']))
                        $country = '_unknown';

                    $flag = Html::img($bundle->baseUrl . '/flags-iso/flat/24/'.$country.'.png');

                    if ($locale)
                        return $flag . '&nbsp;' . $locale['name'];
                    else
                        return $flag . '&nbsp;' . $data->name;
                }
            ], [
                'attribute' => 'is_default',
                'label' => Yii::t('app/modules/translations', 'Is default?'),
                'format' => 'raw',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'is_default',
                    'items' => $searchModel->getStatusesList(true),
                    'options' => [
                        'id' => 'langs-default',
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {

                    if ($data->is_default)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-check text-muted disabled"></span>';

                }
            ], [
                'attribute' => 'is_system',
                'label' => Yii::t('app/modules/translations', 'Is system?'),
                'format' => 'raw',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'is_system',
                    'items' => $searchModel->getStatusesList(true),
                    'options' => [
                        'id' => 'langs-system',
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {

                    if ($data->is_system)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-check text-muted disabled"></span>';

                }
            ], [
                'attribute' => 'is_frontend',
                'label' => Yii::t('app/modules/translations', 'In frontend?'),
                'format' => 'raw',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'is_frontend',
                    'items' => $searchModel->getStatusesList(true),
                    'options' => [
                        'id' => 'langs-frontend',
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    if ($data->is_frontend) {
                        return '<div id="switcher-' . $data->id . '" data-attribute="is_frontend" data-value-current="' . $data->is_frontend . '" data-id="' . $data->id . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" class="btn btn-xs btn-default">OFF</button><button data-value="1" class="btn btn-xs btn-primary">ON</button></div>';
                    } else {
                        return '<div id="switcher-' . $data->id . '" data-attribute="is_frontend" data-value-current="' . $data->is_frontend . '" data-id="' . $data->id . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" class="btn btn-xs btn-danger">OFF</button><button data-value="1" class="btn btn-xs btn-default">ON</button></div>';
                    }
                }
            ], [
                'attribute' => 'status',
                'format' => 'raw',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',
                    'items' => $searchModel->getStatusesList(true),
                    'options' => [
                        'id' => 'langs-status',
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    if ($data->is_system || $data->is_default) {

                        if ($data->status == $data::LANGUAGE_STATUS_ACTIVE)
                            return '<span class="label label-success">' . Yii::t('app/modules/translations', 'Active') . '</span>';
                        else
                            return '<span class="label label-default">' . Yii::t('app/modules/translations', 'Disabled') . '</span>';

                    } else {
                        if ($data->status == $data::LANGUAGE_STATUS_ACTIVE) {
                            return '<div id="switcher-' . $data->id . '" data-attribute="status" data-value-current="' . $data->status . '" data-id="' . $data->id . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" class="btn btn-xs btn-default">OFF</button><button data-value="1" class="btn btn-xs btn-primary">ON</button></div>';
                        } else {
                            return '<div id="switcher-' . $data->id . '" data-attribute="status" data-value-current="' . $data->status . '" data-id="' . $data->id . '" data-toggle="button-switcher" class="btn-group btn-toggle"><button data-value="0" class="btn btn-xs btn-danger">OFF</button><button data-value="1" class="btn btn-xs btn-default">ON</button></div>';
                        }
                    }
                }
            ],


            [
                'attribute' => 'created',
                'label' => Yii::t('app/modules/translations','Created'),
                'format' => 'html',
                'value' => function($data) {

                    $output = "";
                    if ($user = $data->createdBy) {
                        $output = Html::a($user->username, ['../admin/users/view/?id='.$user->id], [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    } else if ($data->created_by) {
                        $output = $data->created_by;
                    }

                    if (!empty($output))
                        $output .= ", ";

                    $output .= Yii::$app->formatter->format($data->updated_at, 'datetime');
                    return $output;
                }
            ],
            [
                'attribute' => 'updated',
                'label' => Yii::t('app/modules/translations','Updated'),
                'format' => 'html',
                'value' => function($data) {

                    $output = "";
                    if ($user = $data->updatedBy) {
                        $output = Html::a($user->username, ['../admin/users/view/?id='.$user->id], [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    } else if ($data->updated_by) {
                        $output = $data->updated_by;
                    }

                    if (!empty($output))
                        $output .= ", ";

                    $output .= Yii::$app->formatter->format($data->updated_at, 'datetime');
                    return $output;
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('app/modules/translations','Actions'),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'visibleButtons' => [
                    'view' => false,
                    'update' => false,
                    'delete' => function($data) {
                        return (($data->is_system || $data->is_default) ? false : true);
                    }
                ],
                'buttons'=> [
                    'delete' => function($url, $data, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::to(['langs/index', 'action' => 'delete', 'id' => $data['id']]), [
                            'title' => Yii::t('yii', 'Delete'),
                            'data' => [
                                'confirm' =>  Yii::t('app/modules/translations', 'Are you sure you want to delete this language? This action will not delete translations.'),
                                'pjax' => '0',
                                'id' => $key
                            ],
                        ]);
                    },
                ],
            ]
        ],
        'pager' => [
            'options' => [
                'class' => 'pagination',
            ],
            'maxButtonCount' => 5,
            'activePageCssClass' => 'active',
            'prevPageCssClass' => '',
            'nextPageCssClass' => '',
            'firstPageCssClass' => 'previous',
            'lastPageCssClass' => 'next',
            'firstPageLabel' => Yii::t('app/modules/translations', 'First page'),
            'lastPageLabel'  => Yii::t('app/modules/translations', 'Last page'),
            'prevPageLabel'  => Yii::t('app/modules/translations', '&larr; Prev page'),
            'nextPageLabel'  => Yii::t('app/modules/translations', 'Next page &rarr;')
        ],
    ]); ?>
    <hr/>
    <div class="modules-add-form">
        <?php $form = ActiveForm::begin([
            'options' => [
                'class' => 'form form-inline'
            ]
        ]); ?>
        <legend><?= Yii::t('app/modules/translations', 'Available languages'); ?></legend>
        <div class="col-xs-6 col-sm-3 col-lg-3">
            <?= $form->field($model, 'languages', [
                'options' => [
                    'tag' => false
                ]])->label(false)->widget(SelectInput::class, [
                'items' => $languages,
                'options' => [
                    'class' => 'form-control',
                    'disabled' => (count($languages) == 0) ? true : false
                ],
                'pluginOptions' => [
                    'dropdownClass' => '.dropdown .btn-block',
                    'toggleClass' => '.btn .btn-default .dropdown-toggle .btn-block',
                    'toggleText' => Yii::t('app/modules/translations', 'Select a language')
                ]
            ]); ?>
        </div>
        <div class="col-xs-6 col-sm-3 col-lg-3">
            <?= $form->field($model, 'autoActivate')->checkbox([
                'checked' => true,
                'style' => 'margin-top:10px;',
                'disabled' => (count($languages) == 0) ? true : false
            ]); ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-lg-3">
            <div class="form-group field-modules-autoactivate">
                <?= Html::submitButton(Yii::t('app/modules/translations', 'Add language'), [
                    'class' => 'btn btn-success',
                    'disabled' => (count($languages) == 0) ? true : false
                ]) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php $this->registerJs(
    'var $container = $("#translationsLangsAjax");
    var requestURL = window.location.href;
    if ($container.length > 0) {
        $container.delegate(\'[data-toggle="button-switcher"] button\', \'click\', function() {
            var id = $(this).parent(\'.btn-group\').data(\'id\');
            var attribute = $(this).parent(\'.btn-group\').data(\'attribute\');
            var value = $(this).data(\'value\');
             $.ajax({
                type: "POST",
                url: requestURL + \'?change=\' + attribute,
                dataType: \'json\',
                data: {\'id\': id, \'value\': value},
                complete: function(data) {
                    $.pjax.reload({type:\'POST\', container:\'#translationsLangsAjax\'});
                }
             });
        });
    }', \yii\web\View::POS_READY
); ?>

<?php echo $this->render('../_debug'); ?>