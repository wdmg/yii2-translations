<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model wdmg\translations\models\Translations */

\yii\web\YiiAsset::register($this);

?>
<div class="options-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'language',
            // 'sources.language',
            'sources.category',
            'sources.message',
            'sources.alias',
            'translation',
            'status',
            'created_at:datetime',
            'created_by',
            'updated_at:datetime',
            'updated_by',
            /*
            'sources.created_at:datetime',
            'sources.created_by',
            'sources.updated_at:datetime',
            'sources.updated_by',*/
        ],
    ]) ?>
    <div class="modal-footer">
        <?= Html::a(Yii::t('app/modules/translations', 'Close'), "#", [
                'class' => 'btn btn-default pull-left',
                'data-dismiss' => 'modal'
            ]);
        ?>
        <?= Html::a(Yii::t('app/modules/translations', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary pull-right']);
        ?>
        <?= Html::a(Yii::t('app/modules/translations', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger pull-right',
                'data' => [
                    'confirm' => Yii::t('app/modules/translations', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]);
        ?>
    </div>
</div>
