<?php

namespace wdmg\translations\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%trans_langs}}".
 *
 * @property int $id
 *
 * @property string $url
 * @property string $locale
 * @property string $name
 * @property integer $is_default
 * @property integer $is_system
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 */
class Languages extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trans_langs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
        ];

        if (class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $behaviors['blameable'] = [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ];
        }

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['url', 'locale', 'name'], 'required'],
            ['url', 'string', 'max' => 3],
            ['locale', 'string', 'max' => 10],
            ['name', 'string', 'max' => 64],
            [['is_default', 'is_system', 'status'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
        ];

        if (class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $rules[] = [['created_by', 'updated_by'], 'required'];
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/translations', 'ID'),
            'url' => Yii::t('app/modules/translations', 'URL'),
            'locale' => Yii::t('app/modules/translations', 'Locale'),
            'name' => Yii::t('app/modules/translations', 'Name'),
            'is_default' => Yii::t('app/modules/translations', 'Is default?'),
            'is_system' => Yii::t('app/modules/translations', 'Is system?'),
            'status' => Yii::t('app/modules/translations', 'Status'),
            'created_at' => Yii::t('app/modules/translations', 'Created at'),
            'created_by' => Yii::t('app/modules/translations', 'Created by'),
            'updated_at' => Yii::t('app/modules/translations', 'Updated at'),
            'updated_by' => Yii::t('app/modules/translations', 'Updated by'),
        ];

    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getUser()
    {
        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users']))
            return $this->hasOne(\wdmg\users\models\Users::className(), ['id' => 'created_by']);
        else
            return null;
    }
}
