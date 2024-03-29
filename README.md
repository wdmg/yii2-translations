[![Yii2](https://img.shields.io/badge/required-Yii2_v2.0.40-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Downloads](https://img.shields.io/packagist/dt/wdmg/yii2-translations.svg)](https://packagist.org/packages/wdmg/yii2-translations)
[![Packagist Version](https://img.shields.io/packagist/v/wdmg/yii2-translations.svg)](https://packagist.org/packages/wdmg/yii2-translations)
![Progress](https://img.shields.io/badge/progress-ready_to_use-green.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-translations.svg)](https://github.com/wdmg/yii2-translations/blob/master/LICENSE)

<img src="./docs/images/yii2-translations.png" width="100%" alt="Yii2 Translations manager" />

# Yii2 Translations
Translate manager for Yii2. The module manages the translations and languages of the system. Outputs language versions to the frontend of the site through its own component. The functionality of the module interacts with other modules and provides multilingual content.

This module is an integral part of the [Butterfly.CMS](https://butterflycms.com/) content management system, but can also be used as an standalone extension.

Copyrights (c) 2019-2023 [W.D.M.Group, Ukraine](https://wdmg.com.ua/)

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.40 and newest
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
            'supportLocales' => ["en", "en-US", "uk", "uk-UA", "ru", "ru-RU", "de", "de-DE", "fr", "fr-FR", "hi", "hi-IN"], // support languages (locales)
            'forceTranslation' => false, // force message translation when the source and target languages are the same
            'sourceLanguage' => 'en-US', // the language of the original messages
            'enableCaching' => false, // enable caching translated messages
            'cachingDuration' => 3600 // time in seconds that the messages can be cached
            'languageScheme' => 'before', // language Scheme (position in URL): before (by default), after, query, subdomain
            'urlManagerConfig' => [], // UrlManager configuration
            'languageOpenGraph' => true, // add OpenGraph markup
            'languageHrefLang' => true, // add HrefLang attribute
            'useExtendedPatterns' => false, // extend search by full code of locale
            'hideDefaultLang' => true, // hide default language locale in URL`s
            ...
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
* v.1.3.1 - Fix url manager for set lang
* v.1.3.0 - Fixes, update dependencies and copyrights
* v.1.2.3 - Update README.md and added/updated languages
