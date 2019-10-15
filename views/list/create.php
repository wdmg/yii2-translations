<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\translations\models\Translations */

$this->title = Yii::t('app/modules/translations', 'Create new translation');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['list/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="translations-create">
    <?= $this->render('_form', [
        'model' => $model,
        'languages' => $languagesList,
        'status' => $statusModes
    ]) ?>
</div>