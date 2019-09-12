<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use wdmg\translations\FlagsAsset;
use wdmg\widgets\SelectInput;
/* @var $this yii\web\View */

$this->title = Yii::t('app/modules/translations', 'Languages list');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['translations/index']];
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
    <?php Pjax::begin(); ?>
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
            ],

            'is_default',
            'is_system',
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
                ]])->label(false)->widget(SelectInput::className(), [
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

<?php echo $this->render('../_debug'); ?>