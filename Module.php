<?php

namespace wdmg\translations;

/**
 * Yii2 Translations
 *
 * @category        Module
 * @version         1.1.1
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-translations
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;
use wdmg\translations\models\Translations;
use wdmg\translations\models\Languages;
use \yii\helpers\FileHelper;

/**
 * Translations module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\translations\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = "langs/index";

    /**
     * @var string, the name of module
     */
    public $name = "Translations";

    /**
     * @var string, the description of module
     */
    public $description = "Translate manager";

    /**
     * @var string the module version
     */
    private $version = "1.1.1";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 1;

    /**
     * @var array, support languages (locales)
     */
    public $locales = ['af', 'af_NA', 'af_ZA', 'ak', 'ak_GH', 'am', 'am_ET', 'ar', 'ar_AE', 'ar_BH', 'ar_DJ', 'ar_DZ', 'ar_EG', 'ar_EH', 'ar_ER', 'ar_IL', 'ar_IQ', 'ar_JO', 'ar_KM', 'ar_KW', 'ar_LB', 'ar_LY', 'ar_MA', 'ar_MR', 'ar_OM', 'ar_PS', 'ar_QA', 'ar_SA', 'ar_SD', 'ar_SO', 'ar_SS', 'ar_SY', 'ar_TD', 'ar_TN', 'ar_YE', 'as', 'as_IN', 'az', 'az_AZ', 'az_Cyrl', 'az_Cyrl_AZ', 'az_Latn', 'az_Latn_AZ', 'be', 'be_BY', 'bg', 'bg_BG', 'bm', 'bm_Latn', 'bm_Latn_ML', 'bn', 'bn_BD', 'bn_IN', 'bo', 'bo_CN', 'bo_IN', 'br', 'br_FR', 'bs', 'bs_BA', 'bs_Cyrl', 'bs_Cyrl_BA', 'bs_Latn', 'bs_Latn_BA', 'ca', 'ca_AD', 'ca_ES', 'ca_FR', 'ca_IT', 'cs', 'cs_CZ', 'cy', 'cy_GB', 'da', 'da_DK', 'da_GL', 'de', 'de_AT', 'de_BE', 'de_CH', 'de_DE', 'de_LI', 'de_LU', 'dz', 'dz_BT', 'ee', 'ee_GH', 'ee_TG', 'el', 'el_CY', 'el_GR', 'en', 'en_AG', 'en_AI', 'en_AS', 'en_AU', 'en_BB', 'en_BE', 'en_BM', 'en_BS', 'en_BW', 'en_BZ', 'en_CA', 'en_CC', 'en_CK', 'en_CM', 'en_CX', 'en_DG', 'en_DM', 'en_ER', 'en_FJ', 'en_FK', 'en_FM', 'en_GB', 'en_GD', 'en_GG', 'en_GH', 'en_GI', 'en_GM', 'en_GU', 'en_GY', 'en_HK', 'en_IE', 'en_IM', 'en_IN', 'en_IO', 'en_JE', 'en_JM', 'en_KE', 'en_KI', 'en_KN', 'en_KY', 'en_LC', 'en_LR', 'en_LS', 'en_MG', 'en_MH', 'en_MO', 'en_MP', 'en_MS', 'en_MT', 'en_MU', 'en_MW', 'en_MY', 'en_NA', 'en_NF', 'en_NG', 'en_NR', 'en_NU', 'en_NZ', 'en_PG', 'en_PH', 'en_PK', 'en_PN', 'en_PR', 'en_PW', 'en_RW', 'en_SB', 'en_SC', 'en_SD', 'en_SG', 'en_SH', 'en_SL', 'en_SS', 'en_SX', 'en_SZ', 'en_TC', 'en_TK', 'en_TO', 'en_TT', 'en_TV', 'en_TZ', 'en_UG', 'en_UM', 'en_US', 'en_VC', 'en_VG', 'en_VI', 'en_VU', 'en_WS', 'en_ZA', 'en_ZM', 'en_ZW', 'eo', 'es', 'es_AR', 'es_BO', 'es_CL', 'es_CO', 'es_CR', 'es_CU', 'es_DO', 'es_EA', 'es_EC', 'es_ES', 'es_GQ', 'es_GT', 'es_HN', 'es_IC', 'es_MX', 'es_NI', 'es_PA', 'es_PE', 'es_PH', 'es_PR', 'es_PY', 'es_SV', 'es_US', 'es_UY', 'es_VE', 'et', 'et_EE', 'eu', 'eu_ES', 'fa', 'fa_AF', 'fa_IR', 'ff', 'ff_CM', 'ff_GN', 'ff_MR', 'ff_SN', 'fi', 'fi_FI', 'fo', 'fo_FO', 'fr', 'fr_BE', 'fr_BF', 'fr_BI', 'fr_BJ', 'fr_BL', 'fr_CA', 'fr_CD', 'fr_CF', 'fr_CG', 'fr_CH', 'fr_CI', 'fr_CM', 'fr_DJ', 'fr_DZ', 'fr_FR', 'fr_GA', 'fr_GF', 'fr_GN', 'fr_GP', 'fr_GQ', 'fr_HT', 'fr_KM', 'fr_LU', 'fr_MA', 'fr_MC', 'fr_MF', 'fr_MG', 'fr_ML', 'fr_MQ', 'fr_MR', 'fr_MU', 'fr_NC', 'fr_NE', 'fr_PF', 'fr_PM', 'fr_RE', 'fr_RW', 'fr_SC', 'fr_SN', 'fr_SY', 'fr_TD', 'fr_TG', 'fr_TN', 'fr_VU', 'fr_WF', 'fr_YT', 'fy', 'fy_NL', 'ga', 'ga_IE', 'gd', 'gd_GB', 'gl', 'gl_ES', 'gu', 'gu_IN', 'gv', 'gv_IM', 'ha', 'ha_GH', 'ha_Latn', 'ha_Latn_GH', 'ha_Latn_NE', 'ha_Latn_NG', 'ha_NE', 'ha_NG', 'he', 'he_IL', 'hi', 'hi_IN', 'hr', 'hr_BA', 'hr_HR', 'hu', 'hu_HU', 'hy', 'hy_AM', 'id', 'id_ID', 'ig', 'ig_NG', 'ii', 'ii_CN', 'is', 'is_IS', 'it', 'it_CH', 'it_IT', 'it_SM', 'ja', 'ja_JP', 'ka', 'ka_GE', 'ki', 'ki_KE', 'kk', 'kk_Cyrl', 'kk_Cyrl_KZ', 'kk_KZ', 'kl', 'kl_GL', 'km', 'km_KH', 'kn', 'kn_IN', 'ko', 'ko_KP', 'ko_KR', 'ks', 'ks_Arab', 'ks_Arab_IN', 'ks_IN', 'kw', 'kw_GB', 'ky', 'ky_Cyrl', 'ky_Cyrl_KG', 'ky_KG', 'lb', 'lb_LU', 'lg', 'lg_UG', 'ln', 'ln_AO', 'ln_CD', 'ln_CF', 'ln_CG', 'lo', 'lo_LA', 'lt', 'lt_LT', 'lu', 'lu_CD', 'lv', 'lv_LV', 'mg', 'mg_MG', 'mk', 'mk_MK', 'ml', 'ml_IN', 'mn', 'mn_Cyrl', 'mn_Cyrl_MN', 'mn_MN', 'mr', 'mr_IN', 'ms', 'ms_BN', 'ms_Latn', 'ms_Latn_BN', 'ms_Latn_MY', 'ms_Latn_SG', 'ms_MY', 'ms_SG', 'mt', 'mt_MT', 'my', 'my_MM', 'nb', 'nb_NO', 'nb_SJ', 'nd', 'nd_ZW', 'ne', 'ne_IN', 'ne_NP', 'nl', 'nl_AW', 'nl_BE', 'nl_BQ', 'nl_CW', 'nl_NL', 'nl_SR', 'nl_SX', 'nn', 'nn_NO', 'no', 'no_NO', 'om', 'om_ET', 'om_KE', 'or', 'or_IN', 'os', 'os_GE', 'os_RU', 'pa', 'pa_Arab', 'pa_Arab_PK', 'pa_Guru', 'pa_Guru_IN', 'pa_IN', 'pa_PK', 'pl', 'pl_PL', 'ps', 'ps_AF', 'pt', 'pt_AO', 'pt_BR', 'pt_CV', 'pt_GW', 'pt_MO', 'pt_MZ', 'pt_PT', 'pt_ST', 'pt_TL', 'qu', 'qu_BO', 'qu_EC', 'qu_PE', 'rm', 'rm_CH', 'rn', 'rn_BI', 'ro', 'ro_MD', 'ro_RO', 'ru', 'ru_BY', 'ru_KG', 'ru_KZ', 'ru_MD', 'ru_RU', 'ru_UA', 'rw', 'rw_RW', 'se', 'se_FI', 'se_NO', 'se_SE', 'sg', 'sg_CF', 'sh', 'sh_BA', 'si', 'si_LK', 'sk', 'sk_SK', 'sl', 'sl_SI', 'sn', 'sn_ZW', 'so', 'so_DJ', 'so_ET', 'so_KE', 'so_SO', 'sq', 'sq_AL', 'sq_MK', 'sq_XK', 'sr', 'sr_BA', 'sr_Cyrl', 'sr_Cyrl_BA', 'sr_Cyrl_ME', 'sr_Cyrl_RS', 'sr_Cyrl_XK', 'sr_Latn', 'sr_Latn_BA', 'sr_Latn_ME', 'sr_Latn_RS', 'sr_Latn_XK', 'sr_ME', 'sr_RS', 'sr_XK', 'sv', 'sv_AX', 'sv_FI', 'sv_SE', 'sw', 'sw_KE', 'sw_TZ', 'sw_UG', 'ta', 'ta_IN', 'ta_LK', 'ta_MY', 'ta_SG', 'te', 'te_IN', 'th', 'th_TH', 'ti', 'ti_ER', 'ti_ET', 'tl', 'tl_PH', 'to', 'to_TO', 'tr', 'tr_CY', 'tr_TR', 'ug', 'ug_Arab', 'ug_Arab_CN', 'ug_CN', 'uk', 'uk_UA', 'ur', 'ur_IN', 'ur_PK', 'uz', 'uz_AF', 'uz_Arab', 'uz_Arab_AF', 'uz_Cyrl', 'uz_Cyrl_UZ', 'uz_Latn', 'uz_Latn_UZ', 'uz_UZ', 'vi', 'vi_VN', 'yi', 'yo', 'yo_BJ', 'yo_NG', 'zh', 'zh_CN', 'zh_HK', 'zh_Hans', 'zh_Hans_CN', 'zh_Hans_HK', 'zh_Hans_MO', 'zh_Hans_SG', 'zh_Hant', 'zh_Hant_HK', 'zh_Hant_MO', 'zh_Hant_TW', 'zh_MO', 'zh_SG', 'zh_TW', 'zu', 'zu_ZA'];

    /**
     * @var bool whether to force message translation when the source and target languages are the same.
     * Defaults to false, meaning translation is only performed when source and target languages are different.
     */
    public $forceTranslation = false;

    /**
     * @var string the language that the original messages are in. If not set, it will use the value of
     * [[\yii\base\Application::sourceLanguage]].
     */
    public $sourceLanguage = 'en-US';

    /**
     * @var bool whether to enable caching translated messages
     */
    public $enableCaching = false;

    /**
     * @var int the time in seconds that the messages can remain valid in cache (only for  use with `DbMessageSource`).
     * Use 0 to indicate that the cached data will never expire.
     * @see enableCaching
     */
    public $cachingDuration = 3600;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);
    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($createLink = false)
    {
        $items = [
            'label' => $this->name,
            'url' => '#',
            'icon' => 'fa fa-fw fa-language',
            'active' => in_array(\Yii::$app->controller->module->id, [$this->id]),
            'items' => [
                [
                    'label' => Yii::t('app/modules/translations', 'Languages list'),
                    'url' => [$this->routePrefix . '/translations/langs/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['translations']) &&  Yii::$app->controller->id == 'langs'),
                ],
                [
                    'label' => Yii::t('app/modules/translations', 'Translations list'),
                    'url' => [$this->routePrefix . '/translations/list/'],
                    'active' => (in_array(\Yii::$app->controller->module->id, ['translations']) &&  Yii::$app->controller->id == 'list'),
                ],
            ]
        ];
        return $items;
    }

    /**
     * Process for missing translations and truing get is from source
     */
    public function missingTranslations($translation, $module = null) {

        if (is_null($module))
            $module = $this;

        // Get source language of app
        if (isset(Yii::$app->params['translations.sourceLanguage']))
            $sourceLanguage = Yii::$app->params['translations.sourceLanguage'];
        else
            $sourceLanguage = $this->sourceLanguage;

        // Get status of force translation
        if (isset(Yii::$app->params['translations.forceTranslation']))
            $forceTranslation = Yii::$app->params['translations.forceTranslation'];
        else
            $forceTranslation = $this->forceTranslation;

        $i18n = Yii::$app->getI18n();
        $translations = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => $sourceLanguage,
            'forceTranslation' => $forceTranslation,
            'basePath' => '@vendor/' . $module->vendor . '/yii2-' . $module->id . '/messages'
        ];

        //$i18n->translations[$translation->category] = ArrayHelper::merge((array) $i18n->translations[$translation->category], $translations);

        if ($i18n->translations[$translation->category])
            return $translation->translatedMessage = $i18n->translate($translation->category.'/*', $translation->message, [], $translation->language);
        else
            return $translation->translatedMessage = $translation->message;

        //$event->translatedMessage = $i18n->translate($event->category, $event->message, [], $event->language);

        /*$i18n = Yii::$app->getI18n();
        $i18n->translate($event->category, $event->message, [], $event->language);*/
        // $event->translatedMessage = '-<@ '.$event->message.' @>-';

        /*$i18n = Yii::$app->getI18n();
        $i18n->translations[$event->category] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/' . $module->vendor . '/yii2-' . $module->id . '/messages'
        ];*/
        // $event->translatedMessage = $i18n->translate($event->category, $event->message, [], $event->language);

    }

    /**
     * Registers translations for module
     */
    public function registerTranslations($module = null, $from_db = false)
    {

        if (is_null($module))
            $module = $this;

        if ($this->moduleLoaded('translations', false) && !$from_db)
            $from_db = true;

        if (($module->id == "translations" && Yii::$app instanceof \yii\console\Application) || Translations::getCount() == 0 || Languages::getCount() == 0)
            $from_db = false;

        // Get source language of app
        if (isset(Yii::$app->params['translations.sourceLanguage']))
            $sourceLanguage = Yii::$app->params['translations.sourceLanguage'];
        else
            $sourceLanguage = $this->sourceLanguage;

        // Get status of force translation
        if (isset(Yii::$app->params['translations.forceTranslation']))
            $forceTranslation = Yii::$app->params['translations.forceTranslation'];
        else
            $forceTranslation = $this->forceTranslation;

        // Get status of caching
        if (isset(Yii::$app->params['translations.enableCaching']))
            $enableCaching = Yii::$app->params['translations.enableCaching'];
        else
            $enableCaching = $this->enableCaching;

        // Get caching duration
        if (isset(Yii::$app->params['translations.cachingDuration']))
            $cachingDuration = intval(Yii::$app->params['translations.cachingDuration']);
        else
            $cachingDuration = intval($this->cachingDuration);

        if ($from_db) {

            Yii::$app->i18n->translations['app/modules/' . $module->id] = [
                'class' => 'yii\i18n\DbMessageSource',
                'sourceLanguage' => $sourceLanguage,
                'sourceMessageTable' => '{{%trans_sources}}',
                'messageTable' => '{{%trans_messages}}',
                'enableCaching' => $enableCaching,
                'cachingDuration' => $cachingDuration,
                'forceTranslation' => $forceTranslation,
                'on missingTranslation' => function ($event) use ($module) {

                    if (YII_ENV == 'dev')
                        $module->missingTranslation[] = $event->message;

                    //$this->missingTranslations($event, $module = null);
                }
            ];
        } else {
            Yii::$app->i18n->translations['app/modules/' . $module->id] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => $sourceLanguage,
                'forceTranslation' => $forceTranslation,
                'basePath' => '@vendor/' . $module->vendor . '/yii2-' . $module->id . '/messages',
                'on missingTranslation' => function ($event) use ($module) {

                    if (YII_ENV == 'dev')
                        $module->missingTranslation[] = $event->message;

                    //$this->missingTranslations($event, $module = null);
                }
            ];
        }

        // Name and description translation of module
        $module->name = Yii::t('app/modules/' . $module->id, $module->name);
        $module->description = Yii::t('app/modules/' . $module->id, $module->description);
    }

    /**
     * Scans all available language translations of the system and returns them.
     *
     * @param $languages array of languages ID
     * @return array of translated messages or null
     */
    public function scanTranslations($languages = [])
    {
        $messages = [];
        if ($i18n = Yii::$app->getI18n()) {

            if (!is_array($languages) || count($languages) == 0)
                return null;

            foreach($i18n->translations as $category => $translation) {
                if (isset($translation->basePath)) {
                    $messagePath = $translation->basePath;
                    foreach ($languages as $language) {
                        $messagePath = Yii::getAlias($messagePath . "/" . $language);
                        if (is_dir($messagePath)) {
                            $translationsFiles = FileHelper::findFiles(FileHelper::normalizePath($messagePath), ['only' => ['*.php']]);
                            foreach ($translationsFiles as $file) {
                                if (file_exists($file))
                                    $messages[$language][$category] = require($file);

                            }
                        }
                    }
                }
            }
            return $messages;
        } else {
            return null;
        }
    }

    /**
     * Returns the all original messages used for translation.
     *
     * @param $messages array, list of translated messages
     * @return array, list of sources messages
     */
    public function getSourceMessages($messages = [])
    {
        $sources = [];
        if ($i18n = Yii::$app->getI18n()) {
            if (!is_array($messages) || count($messages) == 0)
                return null;

            foreach ($messages as $categories) {
                foreach ($categories as $category => $source) {
                    $sourceLanguage = $i18n->getMessageSource($category)->sourceLanguage;
                    $sources[$sourceLanguage][$category] = array_keys($source);
                }
            }
        } else {
            return null;
        }
        return $sources;
    }

    /**
     * Returns all allowed locales
     *
     * @param $locales array, list of locales
     * @param $allowISO boolean, flag for not ignoring ISO locales
     * @return array, list of full info about locales
     */
    public function getLocales($locales = [], $allowISO = false)
    {

        $items = [];
        if (empty($locales)) {
            if(!($locales = $this->getOption('translations.locales')))
                $locales = $this->locales;
        }

        if(!$locales || !is_array($locales))
            return false;

        foreach ($locales as $locale) {

            $locale = str_replace('_', '-', $locale);
            if (!$allowISO && !preg_match('/^[a-z]{2}-[A-Z]{2}$/', $locale))
                continue;

            if (!($in_locale = str_replace('_', '-', Yii::$app->language)))
                $in_locale = 'en';

            if ($item = $this->parseLocale($locale, $in_locale))
                $items[] = $item;

        }
        return $items;
    }

    /**
     * Parses the locale into composite elements
     *
     * @param $locale string, locale to return a display info
     * @param $in_locale string, format locale to use to display info
     * @return array, data collection about locale
     */
    public function parseLocale($locale, $in_locale)
    {
        if ($data = \Locale::parseLocale($locale)) {

            $short = $data['language'];

            if (isset($data['region']))
                $locale = $data['language'].'-'.$data['region'];
            else
                $locale = $data['language'];

            return [
                'short' => $short,
                'locale' => $locale,
                'domain' => (isset($data['region'])) ? mb_strtoupper($data['region']) : '',

                'name' => mb_convert_case(trim(\Locale::getDisplayLanguage($locale, $in_locale)), MB_CASE_TITLE, "UTF-8"),
                'origin' => mb_convert_case(trim(\Locale::getDisplayLanguage($locale, $short)), MB_CASE_TITLE, "UTF-8"),
                'intl' => mb_convert_case(trim(\Locale::getDisplayLanguage($locale, 'en')), MB_CASE_TITLE, "UTF-8"),

                'full' => [
                    'current' => mb_convert_case(trim(\Locale::getDisplayName($locale, $in_locale)), MB_CASE_TITLE, "UTF-8"),
                    'origin' => mb_convert_case(trim(\Locale::getDisplayName($locale, $short)), MB_CASE_TITLE, "UTF-8"),
                    'intl' => mb_convert_case(trim(\Locale::getDisplayName($locale, 'en')), MB_CASE_TITLE, "UTF-8"),
                ],

                'region' => [
                    'current' => trim(\Locale::getDisplayRegion($locale, $in_locale)),
                    'origin' => trim(\Locale::getDisplayRegion($locale, $short)),
                    'intl' => trim(\Locale::getDisplayRegion($locale, 'en')),
                ],

                'variant' => trim(\Locale::getDisplayVariant($locale, $in_locale)),
                'script' => trim(\Locale::getDisplayScript($locale, $in_locale)),
                
                'is_default' => (Yii::$app->sourceLanguage == $locale) ? 1 : 0
            ];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);
    }
}