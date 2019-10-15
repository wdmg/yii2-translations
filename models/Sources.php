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
use yii\behaviors\SluggableBehavior;

/**
 * This is the model class for table "{{%trans_sources}}".
 *
 * @property int $id
 * @property string $language
 * @property string $category
 * @property string $alias
 * @property string $message
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 */
class Sources extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trans_sources}}';
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
            'sluggable' =>  [
                'class' => SluggableBehavior::className(),
                'attribute' => ['message'],
                'slugAttribute' => 'alias',
                'ensureUnique' => true,
                'skipOnEmpty' => true,
                'immutable' => true,
                'value' => function ($event) {
                    return $this->getStringAlias($this->message);
                }
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
            [['category', 'language', 'alias', 'message'], 'required'],
            ['category', 'string', 'max' => 255],
            ['language', 'string', 'max' => 16],
            ['message', 'string'],
            ['alias', 'checkUniqueAlias', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['alias', 'string', 'max' => 32],
            ['alias', 'match', 'pattern' => '/^[A-Za-z0-9\-\_]+$/', 'message' => Yii::t('app/modules/translations','It allowed only Latin alphabet, numbers and the «-», «_» characters.')],
            [['created_at', 'updated_at'], 'safe'],
            //[['alias'], 'required', 'on' => ['create', 'update']],
        ];

        if (class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $rules[] = [['created_by', 'updated_by'], 'required'];
        }

        return $rules;
    }

    public function checkUniqueAlias()
    {
        if (is_null($sources = self::findOne(['id' => $this->id, 'alias' => $this->alias]))) {
            if (!is_null($this->alias) && !is_null($sources = self::findOne(['alias' => $this->alias])))
                $this->addError('alias', Yii::t('app/modules/translations', 'Alias key must be unique.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/translations', 'ID'),
            'language' => Yii::t('app/modules/translations', 'Language'),
            'category' => Yii::t('app/modules/translations', 'Category'),
            'alias' => Yii::t('app/modules/translations', 'Alias key'),
            'message' => Yii::t('app/modules/translations', 'Message'),
            'created_at' => Yii::t('app/modules/translations', 'Created at'),
            'created_by' => Yii::t('app/modules/translations', 'Created by'),
            'updated_at' => Yii::t('app/modules/translations', 'Updated at'),
            'updated_by' => Yii::t('app/modules/translations', 'Updated by'),
        ];
    }

    /**
     * Generate short hash of string
     *
     * @param $string string of translate source
     * @return string or null
     */
    private function getStringHash($string) {
        if (!empty($string))
            return substr(sha1($string), 0, 5) . substr(sha1($string), -1, 6);
        else
            return null;
    }

    /**
     * Generate alias key with hash of string
     *
     * @param $string string of translate source
     * @return string or null
     */
    public function getStringAlias($string) {
        if (!empty($string))
            return Inflector::slug(substr($string, 0, 25), '-', true) .'-'. $this->getStringHash($string);
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
