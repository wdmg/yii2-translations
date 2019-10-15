<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model wdmg\translations\models\Translations */
/* @var $form yii\widgets\ActiveForm */

?>
<?php Pjax::begin(); ?>
<div class="translations-form">
    <?php $form = ActiveForm::begin([
        'id' => "addTranslationForm",
        'enableClientValidation' => true,
        'enableAjaxValidation' => true
    ]); ?>
    <?= $form->field($model, 'language')->widget(SelectInput::className(), [
        'items' => $languages,
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>
    <?= $form->field($model, 'category')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'message')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'translation')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'status')->widget(SelectInput::className(), [
        'items' => $status,
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>

    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/translations', '&larr; Back to list'), ['list/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?= Html::submitButton(Yii::t('app/modules/translations', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php Pjax::end(); ?>

<?php $this->registerJs(<<< JS
$(document).ready(function() {
    function afterValidateAttribute(event, attribute, messages)
    {
        if (attribute.name && !attribute.alias && messages.length == 0) {
            var form = $(event.target);
            $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serializeArray(),
                }
            ).done(function(data) {
                if (data.alias && form.find('#translationsedit-alias').val().length == 0) {
                    form.find('#translationsedit-alias').val(data.alias);
                    form.yiiActiveForm('validateAttribute', 'translationsedit-alias');
                }
            }).fail(function () {
                /*form.find('#options-type').val("");
                form.find('#options-type').trigger('change');*/
            });
            return false; // prevent default form submission
        }
    }
    $("#addTranslationForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>
