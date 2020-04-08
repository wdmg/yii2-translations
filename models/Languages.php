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
 * @property integer $is_frontend
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 */
class Languages extends ActiveRecord
{

    const LANGUAGE_STATUS_DISABLED = 0; // Language is not active flag
    const LANGUAGE_STATUS_ACTIVE = 1; // Language is enabled flag
    const LANGUAGE_IS_DEFAULT = 1; // Language is default flag

    public $languages;
    public $autoActivate;
    public static $current = null; // Переменная, для хранения текущего объекта языка

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
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
            'blameable' =>  [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];

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
            [['autoActivate', 'is_default', 'is_system', 'is_frontend', 'status'], 'boolean'],
            [['autoActivate'], 'default', 'value' => 1],
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
            'is_frontend' => Yii::t('app/modules/translations', 'Frontend?'),
            'status' => Yii::t('app/modules/translations', 'Status'),
            'languages' => Yii::t('app/modules/translations', 'Languages'),
            'autoActivate' => Yii::t('app/modules/translations', '- auto activate'),
            'created_at' => Yii::t('app/modules/translations', 'Created at'),
            'created_by' => Yii::t('app/modules/translations', 'Created by'),
            'updated_at' => Yii::t('app/modules/translations', 'Updated at'),
            'updated_by' => Yii::t('app/modules/translations', 'Updated by'),
        ];

    }

    /**
     * @return integer
     */
    public static function getCount()
    {
        return self::find()->where(['status' => self::LANGUAGE_STATUS_ACTIVE])->count();
    }

    /**
     * Get availables languages
     *
     * @note Function get languages list from DB
     * @param bool $onlyActive
     * @param bool $onlyFrontend
     * @param bool $asArray
     * @return \yii\db\ActiveQuery
     */
    public static function getAllLanguages($onlyActive = true, $onlyFrontend = false, $asArray = false)
    {
        $languages = self::find();

        if ($onlyActive)
            $languages->where([
                'status' => self::LANGUAGE_STATUS_ACTIVE,
            ]);

        if ($onlyFrontend)
            $languages->where([
                'is_frontend' => 1,
                'status' => self::LANGUAGE_STATUS_ACTIVE
            ]);

        $languages->indexBy('locale');

        if ($asArray)
            $languages->asArray();

        return $languages->all();
    }

    /**
     * Get pre-installed languages
     *
     * @note Function get languages list from support locales
     * @param $langs array of available languages
     * @param $support array of support locales
     * @return array of languages
     */
    public static function getOtherLangs($languages = [], $support = [])
    {

        if (!is_array($languages) || !is_array($support))
            return [];

        $output = [];
        foreach ($languages as $locale => $lang) {
            foreach ($support as $data) {
                if (($locale !== $data['locale']))
                    $output[] = $data;
            }
        }

        return array_diff($support, $output);
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'created_by']);
        else
            return $this->created_by;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'updated_by']);
        else
            return $this->updated_by;
    }

    /**
     * Returns a list of languages
     *
     * @return array
     */
    public function getLanguagesList($allLanguages = false)
    {
        $list = [];
        if ($allLanguages) {
            $list = [
                '*' => Yii::t('app/modules/translations', 'All languages')
            ];
        }

        $languages = $this->getAllLanguages(false, false, true);
        $list = ArrayHelper::merge($list, ArrayHelper::map($languages, 'locale', 'name'));

        return $list;
    }

    /**
     * Returns a list of language accessibility statuses
     *
     * @param bool $allStatuses
     * @return array
     */
    public function getStatusesList($allStatuses = false)
    {
        $list = [];
        if ($allStatuses) {
            $list = [
                '*' => Yii::t('app/modules/translations', 'All statuses')
            ];
        }

        $list = ArrayHelper::merge($list, [
            self::LANGUAGE_STATUS_DISABLED => Yii::t('app/modules/translations', 'Not active'),
            self::LANGUAGE_STATUS_ACTIVE => Yii::t('app/modules/translations', 'Active'),
        ]);

        return $list;
    }

    /**
     * Getting the current language object
     *
     * @return array|ActiveRecord|null
     */
    public static function getCurrentLang() {
        if (is_null(self::$current)) {
            self::$current = self::getDefaultLang();
        }
        return self::$current;
    }

    /**
     * Set the current language of the object and the user's locale
     *
     * @param null $url
     */
    public static function setCurrentLang($url = null) {
        $lang = self::getLangByUrl($url);
        self::$current = (is_null($lang)) ? self::getDefaultLang() : $lang;
        Yii::$app->language = self::$current->locale;
    }

    /**
     * Get the default language object
     *
     * @return array|ActiveRecord|null
     */
    public static function getDefaultLang() {
        return self::find()->where(['is_default' => self::LANGUAGE_IS_DEFAULT, 'status' => self::LANGUAGE_STATUS_ACTIVE])->one();
    }

    /**
     * Get a language object by url identifier
     *
     * @param null $url
     * @return array|ActiveRecord|null
     */
    public static function getLangByUrl($url = null)
    {
        if (is_null($url)) {
            return null;
        } else {
            $lang = self::find()->where(['url' => $url, 'status' => self::LANGUAGE_STATUS_ACTIVE])->one();
            if (is_null($lang)) {
                return null;
            } else {
                return $lang;
            }
        }
    }
}
