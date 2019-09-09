<?php

namespace wdmg\translations\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%trans_messages}}".
 *
 * @property int $id
 * @property string $language
 * @property string $translation
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 */
class Translations extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trans_messages}}';
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
            ['id', 'integer', 'max' => 11],
            [['language', 'translation'], 'required'],
            ['language', 'string', 'max' => 16],
            ['translation', 'string'],
            ['status', 'boolean'],
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
            'language' => Yii::t('app/modules/translations', 'Language'),
            'translation' => Yii::t('app/modules/translations', 'Translation'),
            'source' => Yii::t('app/modules/translations', 'Source'),
            'status' => Yii::t('app/modules/translations', 'Status'),
            'created_at' => Yii::t('app/modules/translations', 'Created at'),
            'created_by' => Yii::t('app/modules/translations', 'Created by'),
            'updated_at' => Yii::t('app/modules/translations', 'Updated at'),
            'updated_by' => Yii::t('app/modules/translations', 'Updated by'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getLang()
    {
        if(class_exists('\wdmg\translations\models\Languages'))
            return $this->hasOne(\wdmg\translations\models\Languages::className(), ['locale' => 'language']);
        else
            return null;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getSource()
    {
        if(class_exists('\wdmg\translations\models\Sources'))
            return $this->hasOne(\wdmg\translations\models\Sources::className(), ['id' => 'id']);
        else
            return null;
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
