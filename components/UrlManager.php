<?php

namespace wdmg\translations\components;


/**
 * Yii2 UrlManager
 *
 * @category        Component
 * @version         1.2.1
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-translations
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use function Couchbase\defaultDecoder;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\UrlManager as BaseUrlManager;
use yii\web\UrlNormalizerRedirectException;

class UrlManager extends BaseUrlManager
{
    public $module;
    protected $languages;
    protected $request;

    /**
     * {@inheritdoc}
     */
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
            }

            unset($params['lang']);
        } else {
            // If the language parameter is not specified, then we work with the current language
            $lang = $this->languages->getCurrentLang();
        }

        $url = parent::createUrl($params);
        if ($url && isset($this->module->languageScheme) && !is_null($lang)) {

            if ($lang->is_default && $this->module->hideDefaultLang)
                $lang->url = null;

            $sheme = $this->module->languageScheme;
            switch ($sheme) {
                case "after":

                    return ($url == '/') ?  // `http://example.com/site/index/en`
                        (
                            ($lang->url) ?
                            $lang->url :
                            ''
                        ) :
                        $url . (
                            ($lang->url) ?
                            '/' . $lang->url :
                            ''
                        );

                case "query":

                    if ($lang->url) // `http://example.com/site/index/?lang=en`
                        return ($url == '/') ? '?lang=' . $lang->url : $url . '?lang='. $lang->url;
                    else
                        return $url;

                case "subdomain":

                    if (!$baseUrl = trim($this->baseUrl))
                        $baseUrl = Url::base(true);

                    if ($withScheme) { // `http://en.example.com/site/index`

                        if (($lang->url) && mb_strpos($url, $baseUrl) !== false)
                            return str_replace('://', '://' . $lang->url . '.', $url);
                        elseif ($lang->url)
                            return str_replace('://', '://' . $lang->url . '.', $baseUrl) . $url;
                        else
                            return $baseUrl . $url;

                    } else { // `http://en.example.com/site/index`

                        if ($lang->url)
                            $subdomain = str_replace('://', '://' . $lang->url . '.', $baseUrl);
                        else
                            $subdomain = $baseUrl;

                        return ($url == '/') ? $subdomain . '/' : $subdomain . $url;
                    }

                default:

                    if (!$baseUrl = trim($this->baseUrl))
                        $baseUrl = Url::base(true);

                    if ($withScheme) {  // `http://example.com/en/site/index`
                        if (mb_strpos($url, $baseUrl) !== false)
                            return str_replace($baseUrl, $baseUrl . (($lang->url) ? '/' . $lang->url : ''), $url);
                        else
                            return $baseUrl . (($lang->url) ? '/' . $lang->url : '') . $url;
                    } else {
                        return ($url == '/') ?
                            (($lang->url) ? '/' . $lang->url : '') :
                            (($lang->url) ? '/' . $lang->url : '') . $url; // `/en` or `/en/site/index`
                    }

            }

        } else {
            return ($url == '/') ? '/' : '/' . $url; // `/` or `/site/index`
        }
    }


    /**
     * Prepares a pattern for locating a locale from a URL to a front-end
     *
     * @param null $locales array of locales available for definition
     * @param null $scheme the selected scheme for building the URL of multilingual resources
     * @return string|null
     */
    protected function preparePattern($locales = null, $scheme = null)
    {
        if (empty($locales))
            return null;

        $parts = [];
        foreach ($locales as $key => $value) {

            // If the locale is an array key, use it as a value, if not
            // try to use the value of the array.
            $value = (is_string($key)) ? $key : ((!is_null($value) ? $value : ''));

            // Otherwise, skip the current loop iteration.
            if (empty($value))
                continue;

            // We build all possible patterns of language definition
            if (substr($value, -2) === '-*') {
                $locale = substr($value, 0, -2);
                $parts[] = $locale . "\-[a-z]{2,3}";
                $parts[] = $locale;
            } else {
                $parts[] = $value;
            }
        }

        // We sort the language locales by the length of the string, so more accurate entries will be at the beginning of the array
        usort($parts, function($a, $b) {
            return (mb_strlen($a) == mb_strlen($b)) ? 0 : ((mb_strlen($a) < mb_strlen($b)) ? 1 : -1);
        });

        // Glue the chain into the original pattern, depending on the selected scheme
        switch ($scheme) {
            case 'after' :
                return "#(" . implode('|', $parts) . ")\b(/?)$#i";
            case 'query' :
                return "#(" . implode('|', $parts) . ")\b(/?)$#i";
            case 'subdomain' :
                return "#^(?:http[s]*\:\/\/)*(" . implode('|', $parts) . ")\.(?=[^\/]*\..{2,5})#i";
            default:
                return "#^(" . implode('|', $parts) . ")\b(/?)#i";
        }
    }

    /**
     * Parses the current request and determines the language version of the resource that is being requested.
     *
     * @param \yii\web\Request $request
     * @return array|bool
     * @throws InvalidConfigException
     * @throws \yii\base\ExitException
     */
    public function parseRequest($request)
    {

        $language = null;
        $this->request = $request;
        $request_url = $this->request->getAbsoluteUrl();
        $request_path = $this->request->getPathInfo();
        $request_query = $this->request->getQueryParams();

        // Get the available languages for the frontend.
        $languages = $this->languages->getAllLanguages(true, true, true);

        // We retrieve only language locales
        $locales = ArrayHelper::map($languages, 'url', 'locale');

        // Use an extended list of locales, if necessary
        if ($this->module->useExtendedPatterns)
            $locales = ArrayHelper::merge(ArrayHelper::map($languages, 'locale', 'locale'), $locales);

        // Multilingual Resource URL Layout
        $scheme = $this->module->languageScheme;

        // We get the pattern for recognizing the installed language by URL
        $pattern = $this->preparePattern($locales, $scheme);

        // We analyze the current URL for the presence of a language locale
        if (is_string($pattern)) {

            $locale = null;
            if ($scheme == 'query' && isset($request_query['lang'])) {
                if (preg_match($pattern, trim($request_query['lang']), $matches)) {
                    $locale = $matches[1];
                }
            } else if ($scheme == 'subdomain' && preg_match($pattern, $request_url, $matches)) {
                $locale = $matches[1];
            } else if (preg_match($pattern, $request_path, $matches) && in_array($scheme, ['before', 'after'])) {
                $locale = $matches[1];
            }

            // Determined the language locale from the URL
            if (!is_null($locale) && isset($locales[$locale])) {

                // If the locale is different from the language version of the ID in the URL
                // do redirects to a URL with the correct identifier, eg
                // URL http://example.com/en-us/site/index should be redirected to http://example.com/en/site/index
                if (in_array($scheme, ['before', 'after', 'query']) && isset($languages[$locale]["url"])) {
                    if ($languages[$locale]["url"] !== $locale) {
                        $url = str_replace($locale, '', $this->request->url);
                        $url = $this->createUrl([$url, 'lang' => $locale], true);
                        Yii::$app->getResponse()->redirect($url);
                        Yii::$app->end();
                    }
                }

                // If the language was found, install it as the application language
                // and as a locale for forming other links
                if ($locales[$locale]) {
                    $this->languages::setCurrentLang($locale);
                }
            } /* else {
                // Throw an exception if the language is not defined, or the language is not available for front-end
                throw new NotFoundHttpException();
            }*/
        }

        return parent::parseRequest($request);
    }
}