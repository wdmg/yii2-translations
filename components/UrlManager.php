<?php

namespace wdmg\translations\components;


/**
 * Yii2 UrlManager
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
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\Cookie;
use yii\web\UrlManager as BaseUrlManager;
use yii\web\UrlNormalizerRedirectException;

class UrlManager extends BaseUrlManager
{
    public $module;
    protected $languages;

    public function init()
    {

        if (!($this->module = Yii::$app->getModule('admin/translations')))
            $this->module = Yii::$app->getModule('translations');

        $this->languages = new \wdmg\translations\models\Languages;

        return parent::init();
    }

    public function createUrl($params, $withScheme = false)
    {

        if ($withScheme)
            return parent::createAbsoluteUrl($params);

        $lang = null;
        if (isset($params['lang'])) {
            // Если указан идентефикатор языка, то делаем попытку найти язык в БД,
            // иначе работаем с языком по умолчанию
            if (!($lang = $this->languages->findOne(['locale' => $params['lang']]))) {
                $lang = $this->languages->getDefaultLang();
                $lang = Yii::$app->sourceLanguage;
            }

            unset($params['lang']);
        } else {
            // Если не указан параметр языка, то работаем с текущим языком
            $lang = $this->languages->getCurrentLang();
        }

        $url = parent::createUrl($params);

        if (isset($this->module->languageScheme) && !is_null($lang)) {
            switch ($this->module->languageScheme) {
                case "after":
                    return ($url == '/') ? $lang->url : $url . $lang->url; // `/en` or `/site/index/en`

                case "query":
                    return ($url == '/') ? '?lang=' . $lang->url : $url . '?lang='. $lang->url; // `/?lang=en` or `/site/index/?lang=en`

                case "subdomain":

                    if ($withScheme) {
                        $subdomain = str_replace('://', '://' . $lang->url . '.', $url); // `http://en.example.com/` or `http://en.example.com/site/index`
                        return $subdomain;
                    } else {
                        $subdomain = str_replace('://', '://' . $lang->url . '.', Url::base(true)); // `http://en.example.com/` or `http://en.example.com/site/index`
                        return ($url == '/') ? $subdomain . '/' : $subdomain . $url;
                    }

                default:

                    if ($withScheme)
                        return $url; // `http://example.com/en` or `http://example.com/en/site/index`
                    else
                        return ($url == '/') ? '/' . $lang->url : '/' . $lang->url . $url; // `/en` or `/en/site/index`

            }
        } else {
            if ($withScheme)
                return ($url == '/') ? '/' : '/' . $url; // `http://example.com/` or `http://example.com/site/index`
            else
                return ($url == '/') ? '/' : '/' . $url; // `/` or `/site/index`
        }
    }
}