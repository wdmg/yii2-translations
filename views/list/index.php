<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use wdmg\translations\FlagsAsset;
use wdmg\widgets\SelectInput;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */

$this->title = Yii::t('app/modules/translations', 'Translations list');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['list/index']];
$this->params['breadcrumbs'][] = $this->title;

$bundle = FlagsAsset::register($this);
$module = $this->context->module;

?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="translations-list-index">
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'languages',
                'label' => Yii::t('app/modules/translations','Language'),
                'enableSorting' => true,
                'filter' => true,
                'format' => 'raw',
                'value' => function($data) use ($module, $bundle) {
                    $locale = $module->parseLocale($data->languages->locale, Yii::$app->language);
                    if (!($country = $locale['domain']))
                        $country = '_unknown';

                    $flag = Html::img($bundle->baseUrl . '/flags-iso/flat/24/'.$country.'.png');

                    if ($locale)
                        return $flag . '&nbsp;' . $locale['name'];
                    else
                        return $flag . '&nbsp;' . $data->languages->name;
                }
            ],
            [
                'attribute' => 'category',
                'label' => Yii::t('app/modules/translations','Category'),
                'enableSorting' => true,
                'filter' => true,
                'value' => 'sources.category',
            ],
            [
                'attribute' => 'alias',
                'label' => Yii::t('app/modules/translations','Alias key'),
                'enableSorting' => true,
                'filter' => true,
                'value' => 'sources.alias',
            ],
            [
                'attribute' => 'sources',
                'label' => Yii::t('app/modules/translations','Source'),
                'enableSorting' => true,
                'filter' => true,
                'value' => 'sources.message',
            ],
            'translation:ntext',
            [
                'attribute' => 'status',
                'label' => Yii::t('app/modules/translations', 'Status'),
                'filter' => true,
                'format' => 'html',
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {

                    if ($data->status)
                        return '<span class="glyphicon glyphicon-check text-success"></span>';
                    else
                        return '<span class="glyphicon glyphicon-check text-muted"></span>';

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
                'buttons'=> [
                    'view' => function($url, $data, $key) use ($module) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::to(['list/view', 'id' => $data['id']]), [
                            'class' => 'translation-details-link',
                            'title' => Yii::t('yii', 'View'),
                            'data-toggle' => 'modal',
                            'data-target' => '#translationDetails',
                            'data-id' => $key,
                            'data-pjax' => '1'
                        ]);
                    }
                ],
            ]
        ],
        'pager' => [
            'options' => [
                'class' => 'pagination',
            ],
            'maxButtonCount' => 5,
            'activePageCssClass' => 'active',
            'prevPageCssClass' => 'prev',
            'nextPageCssClass' => 'next',
            'firstPageCssClass' => 'first',
            'lastPageCssClass' => 'last',
            'firstPageLabel' => Yii::t('app/modules/translations', 'First page'),
            'lastPageLabel'  => Yii::t('app/modules/translations', 'Last page'),
            'prevPageLabel'  => Yii::t('app/modules/translations', '&larr; Prev page'),
            'nextPageLabel'  => Yii::t('app/modules/translations', 'Next page &rarr;')
        ],
    ]); ?>
    <?php Pjax::end(); ?>
    <hr/>
    <div>
        <div class="btn-group" style="padding:0 15px 15px 0;">
            <?= Html::a(Yii::t('app/modules/translations', 'Scan/re-scan translations'), ['list/scan'], [
                'class' => 'btn btn-primary',
                'disabled' => ($languages::getCount()) ? false : true,
                'data' => [
                    'confirm' => Yii::t('app/modules/translations', 'New translations will be added, and existing ones will be replaced with the original ones. Are you sure you want to scan?'),
                    'method' => 'get',
                ],
            ]) ?>
            <?= Html::a(Yii::t('app/modules/translations', 'Delete all translations'), ['list/clear'], [
                'class' => 'btn btn-danger',
                'disabled' => ($searchModel::getCount()) ? false : true,
                'data' => [
                    'confirm' => Yii::t('app/modules/translations', 'Are you sure you want to delete all items?'),
                    'method' => 'get',
                ],
            ]) ?>
        </div>
        <?= Html::a(Yii::t('app/modules/translations', 'Add new translation'), ['list/create'], ['class' => 'btn btn-success pull-right']) ?>
    </div>
</div>

<?php $this->registerJs(<<< JS
$('body').delegate('.translation-details-link', 'click', function(event) {
    event.preventDefault();
    $.get(
        $(this).attr('href'), 
        function (data) {
            $('#translationDetails .modal-body').html($(data).remove('.modal-footer'));
            if ($(data).find('.modal-footer').length > 0) {
                $('#translationDetails').find('.modal-footer').remove();
                $('#translationDetails .modal-content').append($(data).find('.modal-footer'));
            }
            $('#translationDetails').modal();
        }
    );
});
JS
); ?>

<?php Modal::begin([
    'id' => 'translationDetails',
    'header' => '<h4 class="modal-title">'.Yii::t('app/modules/translations', 'Translation details').'</h4>',
    'footer' => '<a href="#" class="btn btn-default pull-left" data-dismiss="modal">'.Yii::t('app/modules/translations', 'Close').'</a>',
    'clientOptions' => [
        'show' => false
    ]
]); ?>
<?php Modal::end(); ?>

<?php echo $this->render('../_debug'); ?>