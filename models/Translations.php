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
    const TRANSLATION_STATUS_DISABLED = 0; // Translation not active
    const TRANSLATION_STATUS_ACTIVE = 1; // Translation active

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
            ['id', 'integer'],
            [['language', 'translation'], 'required'],
            ['language', 'string', 'max' => 16],
            ['translation', 'string'],
            ['status', 'boolean'],
            [['created_at', 'updated_at'], 'safe'],


            [['first_name',], 'required', 'on'=>['create','update']],  // create scenario
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
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @return integer
     */
    public static function getCount()
    {
        return self::find()->where(['status' => self::TRANSLATION_STATUS_ACTIVE])->count();
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getLanguages()
    {
        if(class_exists('\wdmg\translations\models\Languages'))
            return $this->hasOne(\wdmg\translations\models\Languages::className(), ['locale' => 'language']);
        else
            return null;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        if(class_exists('\wdmg\translations\models\Sources'))
            return $this->hasOne(\wdmg\translations\models\Sources::className(), ['id' => 'id']);
        else
            return null;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getSources()
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

    public static function getLanguagesList($addAllLabel = true) {

        $items = [];
        if ($addAllLabel)
            $items = ['*' => Yii::t('app/modules/translations', 'All languages')];

        $languages = new \wdmg\translations\models\Languages();
        $list = $languages->find()->select(['locale', 'name'])->where(['status' => $languages::LANGUAGE_STATUS_ACTIVE])->asArray()->all();
        foreach($list as $item) {
            $items[$item['locale']] = $item['name'];
        }
        return $items;
    }

    public static function getStatusModeList($addAllLabel = true) {

        $items = [];
        if ($addAllLabel)
            $items = ['*' => Yii::t('app/modules/translations', 'All modes')];

        return ArrayHelper::merge($items, [
            self::TRANSLATION_STATUS_ACTIVE => Yii::t('app/modules/translations', 'Active'),
            self::TRANSLATION_STATUS_DISABLED => Yii::t('app/modules/translations', 'Not active'),
        ]);
    }
}
