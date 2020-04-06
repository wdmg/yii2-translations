<?php

namespace wdmg\translations\components;


/**
 * Yii2 Translations
 *
 * @category        Component
 * @version         1.1.2
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
     * Return active locales as AR object or array
     *
     * @param bool $onlyFrontend
     * @param bool $asArray
     * @return mixed
     */
    public function getLocales($onlyActive = false, $onlyFrontend = true, $asArray = false)
    {
        return $this->languages->getAllLanguages($onlyActive, $onlyFrontend, $asArray);
    }

    public function parseLocale($locale, $in_locale)
    {
        return $this->module->parseLocale($locale, $in_locale);
    }

    public function getLanguages($allLanguages = false)
    {
        return $this->languages->getLanguagesList($allLanguages);
    }

}

?>