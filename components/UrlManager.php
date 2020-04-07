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

    /**
     * Generates an absolute URL based on language settings.
     *
     * @param array|string $params
     * @param null $scheme
     * @return string
     * @throws InvalidConfigException
     */
    public function createAbsoluteUrl($params, $scheme = null)
    {
        $params = (array) $params;
        $url = self::createUrl($params, true);

        if (strpos($url, '://') === false) {
            $hostInfo = $this->getHostInfo();
            if (strncmp($url, '//', 2) === 0) {
                $url = substr($hostInfo, 0, strpos($hostInfo, '://')) . ':' . $url;
            } else {
                $url = $hostInfo . $url;
            }
        }

        return Url::ensureScheme($url, $scheme);
    }

    /**
     * Generates a URL based on language settings. Also used as a helper function for the createAbsoluteUrl() method.
     *
     * @param array|string $params
     * @param bool $withScheme
     * @return mixed|string
     */
    public function createUrl($params, $withScheme = false)
    {
        $lang = null;
        if (isset($params['lang'])) {
            // If the language identifier is specified, then we try to find the language in the database,
            // otherwise we work with the default language
            if (!($lang = $this->languages->findOne(['locale' => $params['lang']]))) {
                $lang = $this->languages->getDefaultLang();
                $lang = Yii::$app->sourceLanguage;
            }

            unset($params['lang']);
        } else {
            // If the language parameter is not specified, then we work with the current language
            $lang = $this->languages->getCurrentLang();
        }

        $url = parent::createUrl($params);
        if ($url && isset($this->module->languageScheme) && !is_null($lang)) {

            switch ($this->module->languageScheme) {
                case "after":

                    return ($url == '/') ? $lang->url : $url . $lang->url; // `http://example.com/site/index/en`

                case "query":

                    return ($url == '/') ? '?lang=' . $lang->url : $url . '?lang='. $lang->url; // `http://example.com/site/index/?lang=en`

                case "subdomain":

                    if (!$baseUrl = trim($this->baseUrl))
                        $baseUrl = Url::base(true);

                    if ($withScheme) { // `http://en.example.com/site/index`
                        if (mb_strpos($url, $baseUrl) !== false)
                            return str_replace('://', '://' . $lang->url . '.', $url);
                        else
                            return str_replace('://', '://' . $lang->url . '.', $baseUrl) . $url;
                    } else { // `http://en.example.com/site/index`
                        $subdomain = str_replace('://', '://' . $lang->url . '.', $baseUrl);
                        return ($url == '/') ? $subdomain . '/' : $subdomain . $url;
                    }

                default:

                    if (!$baseUrl = trim($this->baseUrl))
                        $baseUrl = Url::base(true);

                    if ($withScheme) {  // `http://example.com/en/site/index`
                        if (mb_strpos($url, $baseUrl) !== false)
                            return str_replace($baseUrl, $baseUrl . '/' . $lang->url, $url);
                        else
                            return $baseUrl . '/' . $lang->url . $url;
                    } else {
                        return ($url == '/') ? '/' . $lang->url : '/' . $lang->url . $url; // `/en` or `/en/site/index`
                    }

            }

        } else {
            return ($url == '/') ? '/' : '/' . $url; // `/` or `/site/index`
        }
    }
}