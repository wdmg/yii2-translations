<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */

$this->title = Yii::t('app/modules/translations', 'Translations list');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['translations/index']];
$this->params['breadcrumbs'][] = $this->title;

$translations = $this->context->module->scanTranslations(['ru-RU']);
var_dump($this->context->module->getSourceMessages($translations));

?>

<p><?= Yii::t('app/frontend', 'Welcome!'); ?></p>
<p><?= Yii::t('app/frontend', 'Is text about company...'); ?></p>
<p><?= Yii::t('app/frontend', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.'); ?></p>

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
                'attribute' => 'lang',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->lang->name;
                }
            ],

            [
                'attribute' => 'source',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->source->message;
                }
            ],

            'translation:ntext',
            'status',

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('app/modules/translations','Actions'),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
            ]
        ]
    ]); ?>
    <hr/>
    <div>
        <?= Html::a(Yii::t('app/modules/translations', 'Add translation'), ['list/create'], ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php echo $this->render('../_debug'); ?>