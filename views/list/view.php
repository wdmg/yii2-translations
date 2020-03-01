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
