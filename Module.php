<?php

namespace wdmg\translations;

/**
 * Yii2 Translations
 *
 * @category        Module
 * @version         1.0.0
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-translations
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;
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
    public $defaultRoute = "list/index";

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
    private $version = "1.0.0";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 2;

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
            'icon' => 'fa-language',
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
                                //$category = pathinfo($file, PATHINFO_FILENAME);
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
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        //var_dump($this->scanTranslations());
        parent::bootstrap($app);
    }
}