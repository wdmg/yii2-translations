[![Progress](https://img.shields.io/badge/required-Yii2_v2.0.13-blue.svg)](https://packagist.org/packages/yiisoft/yii2) [![Github all releases](https://img.shields.io/github/downloads/wdmg/yii2-translations/total.svg)](https://GitHub.com/wdmg/yii2-translations/releases/) [![GitHub version](https://badge.fury.io/gh/wdmg%2Fyii2-translations.svg)](https://github.com/wdmg/yii2-translations) ![Progress](https://img.shields.io/badge/progress-in_development-red.svg) [![GitHub license](https://img.shields.io/github/license/wdmg/yii2-translations.svg)](https://github.com/wdmg/yii2-translations/blob/master/LICENSE)

# Yii2 Translations
Translate manager for Yii2

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.20 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-translations"`

After configure db connection, run the following command in the console:

`$ php yii translations/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations
  3) Scan and add translations

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-translations/migrations`

# Configure

To add a module to the project, add the following data in your configuration file:

    
    'modules' => [
        'translations' => [
            'class' => 'wdmg\translations\Module',
            'routePrefix' => 'admin',
            'locales' => ["en", "en-US", "uk", "uk-UA", "ru", "ru-RU", "de", "de-DE", "fr", "fr-FR", "hi", "hi-IN"], // support languages (locales)
            'forceTranslation' => false, // force message translation when the source and target languages are the same
            'sourceLanguage' => 'en-US', // the language of the original messages
            'enableCaching' => false, // enable caching translated messages
            'cachingDuration' => 3600 // time in seconds that the messages can be cached
        ],
        ...
    ],

# Routing
Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('translations')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [ready to use]
* v.1.1.0 - CRUD for translations
* v.1.0.1 - Added console scan for sources and translations
* v.1.0.0 - Added base module, model, controllers and migrations