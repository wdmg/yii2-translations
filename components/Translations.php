<?php

namespace wdmg\translations\components;


/**
 * Yii2 Translations
 *
 * @category        Component
 * @version         1.2.2
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-translations
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class Translations extends Component
{
    public $module;
    protected $languages;
    protected $translations;
    protected $sources;

    /**
     * Initialize the component
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!($this->module = Yii::$app->getModule('admin/translations')))
            $this->module = Yii::$app->getModule('translations');

        $this->languages = new \wdmg\translations\models\Languages;
        $this->translations = new \wdmg\translations\models\Translations;
        $this->sources = new \wdmg\translations\models\Sources;
    }

    /**
     * Return locales as AR object or array
     *
     * @param bool $onlyFrontend
     * @param bool $asArray
     * @return mixed
     */
    public function getLocales($onlyActive = false, $onlyFrontend = true, $asArray = false)
    {
        return $this->languages->getAllLanguages($onlyActive, $onlyFrontend, $asArray);
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
        return $this->module->parseLocale($locale, $in_locale);
    }

    /**
     * Returns a list of available languages
     *
     * @param bool $allLanguages
     * @return mixed
     */
    public function getLanguages($allLanguages = false)
    {
        return $this->languages->getLanguagesList($allLanguages);
    }

    /**
     * Returns the default language locale
     *
     * @return string|null
     */
    public function getDefaultLang()
    {
        if (($lang = $this->languages->getDefaultLang()) !== null) {
            return $lang->url;
        } else {
            return null;
        }
    }

}

?>