<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\translations\models\Translations */

$this->title = Yii::t('app/modules/translations', 'Update translation for: {translation}', [
    'translation' => $model->message,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/translations', 'Translations list'), 'url' => ['list/index']];
$this->params['breadcrumbs'][] = Yii::t('app/modules/translations', 'Edit');

?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="translations-update">
    <?= $this->render('_form', [
        'model' => $model,
        'languages' => $languagesList,
        'status' => $statusModes
    ]) ?>

</div>