<?php

namespace wdmg\translations\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

use yii\helpers\Console;
use yii\helpers\ArrayHelper;
use wdmg\translations\models\Languages;
use wdmg\translations\models\Sources;
use wdmg\translations\models\Translations;

class InitController extends Controller
{
    /**
     * @inheritdoc
     */
    public $choice = null;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'index';

    public function options($actionID)
    {
        return ['choice', 'color', 'interactive', 'help'];
    }

    public function actionIndex($params = null)
    {
        $module = Yii::$app->controller->module;
        $version = $module->version;
        $welcome =
            '╔════════════════════════════════════════════════╗'. "\n" .
            '║                                                ║'. "\n" .
            '║          TRANSLATIONS MODULE, v.'.$version.'          ║'. "\n" .
            '║          by Alexsander Vyshnyvetskyy           ║'. "\n" .
            '║       (c) 2019-2021 W.D.M.Group, Ukraine       ║'. "\n" .
            '║                                                ║'. "\n" .
            '╚════════════════════════════════════════════════╝';
        echo $name = $this->ansiFormat($welcome . "\n\n", Console::FG_GREEN);
        echo "Select the operation you want to perform:\n";
        echo "  1) Apply all module migrations\n";
        echo "  2) Revert all module migrations\n";
        echo "  3) Scan/re-scan and add translations\n";
        echo "  4) Delete all translations\n\n";
        echo "Your choice: ";

        if(!is_null($this->choice))
            $selected = $this->choice;
        else
            $selected = trim(fgets(STDIN));

        if ($selected == "1") {
            Yii::$app->runAction('migrate/up', ['migrationPath' => '@vendor/wdmg/yii2-translations/migrations', 'interactive' => true]);
        } else if($selected == "2") {
            Yii::$app->runAction('migrate/down', ['migrationPath' => '@vendor/wdmg/yii2-translations/migrations', 'interactive' => true]);
        } else if($selected == "3") {

            $db = Yii::$app->db;
            $langList = null;
            $languagesModel = new Languages();
            foreach ($languagesModel->find()->where(['status' => 1])->select('locale')->asArray()->groupBy('locale')->all() as $locale) {
                foreach ($locale as $lang) {
                    $langList[] = $lang;
                }
            }

            if (empty($langList)) {
                echo "\n";
                echo "No installed languages were found in the system. Want to add is now? (yes|no) [no]: ";
                $selected = trim(fgets(STDIN));
                if ($selected == "y" || $selected == "yes") {
                    echo "\n";
                    echo "Please enter the language identifiers according to the standard RFC 3066, separated by space , like `en-US ru-RU`: ";

                    $inputLangs = explode(' ', trim(fgets(STDIN)));
                    $languages = [];
                    $insertRows = [];
                    $langsCount = 0;
                    if (!empty($inputLangs)) {
                        echo "\n";

                        foreach ($inputLangs as $inputLang) {
                            $displayLanguage = locale_get_display_language($inputLang, 'en');
                            if ($displayLanguage) {

                                $locale = \locale_parse($inputLang);
                                $languages[] = [
                                    'url' => $locale['language'],
                                    'locale' => $locale['language'].'-'.$locale['region'],
                                    'name' => $displayLanguage,
                                ];

                                echo "   - " . $displayLanguage ." (" .$locale['language'].'-'.$locale['region']. ")" . ((Yii::$app->sourceLanguage == ($locale['language'].'-'.$locale['region'])) ? ' as source language' : '') . "\n";
                            }

                        }
                        echo "\n\n";

                        echo "Want to install listened language(s)? (yes|no) [no]: ";
                        $selected = trim(fgets(STDIN));
                        if ($selected == "y" || $selected == "yes") {

                            foreach ($languages as $lang) {
                                $languagesModel = new Languages();
                                $languagesModel->url = $lang['url'];
                                $languagesModel->locale = $lang['locale'];
                                $languagesModel->name = $lang['name'];
                                $languagesModel->is_default = (Yii::$app->sourceLanguage == $lang['locale']) ? 1 : 0;
                                $languagesModel->is_system = 1;
                                //$languagesModel->status = (Yii::$app->sourceLanguage == $lang['locale']) ? 0 : 1;
                                $languagesModel->status = 1;
                                $languagesModel->created_at = new yii\db\Expression('NOW()');
                                $languagesModel->created_by = 0;
                                $languagesModel->updated_at = new yii\db\Expression('NOW()');
                                $languagesModel->updated_by = 0;
                                if ($languagesModel->validate()) {
                                    $insertRows[] = $languagesModel;
                                    $langsCount++;
                                } else {
                                    echo var_export($translationsModel->errors, true);
                                }
                            }

                            $sourcesModel = new Languages();
                            $sql = $db->queryBuilder->batchInsert($languagesModel::tableName(), $languagesModel->attributes(), $insertRows);
                            $db->createCommand($sql . ' ON DUPLICATE KEY UPDATE ' .
                                implode(', ',
                                    array_map(function($attribute) {
                                        return $attribute . ' = VALUES(' . $attribute . ')';
                                    }, $languagesModel->attributes())
                                )
                            )->execute();

                            echo "Added/updated languages: " . $langsCount . "\n";



                        }
                    }

                    // Retry to get the system languages
                    foreach ($languagesModel->find()->where(['status' => 1])->select('locale')->asArray()->groupBy('locale')->all() as $locale) {
                        foreach ($locale as $lang) {
                            $langList[] = $lang;
                        }
                    }

                }
            }

            if (empty($langList)) {
                echo $this->ansiFormat("Error! It is not possible to scan and add translations without installed system languages.\n\n", Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            } else {

                // @TODO: Split this stuff to standalone method

                // Get available translations
                $translationsList = [];
                foreach ($langList as $lang) {
                    $translationsList = ArrayHelper::merge($translationsList, $module->scanTranslations([$lang]));
                }

                // Get source of translations
                $sourcesList = $module->getSourceMessages($translationsList);

                $sourcesIds = [];
                $insertRows = [];
                $sourceCount = 0;
                if (is_countable($sourcesList)) {
                    foreach ($sourcesList as $lang => $sources) {
                        foreach ($sources as $category => $messages) {
                            foreach ($messages as $message) {
                                $sourcesModel = new Sources();
                                $sourcesModel->language = $lang;
                                $sourcesModel->category = $category;
                                $sourcesModel->message = $message;
                                $sourcesModel->alias = $sourcesModel->getStringAlias($message);  // @TODO: Issue, where alias key must be unique.
                                $sourcesModel->created_at = new yii\db\Expression('NOW()');
                                $sourcesModel->created_by = 0;
                                $sourcesModel->updated_at = new yii\db\Expression('NOW()');
                                $sourcesModel->updated_by = 0;
                                if ($sourcesModel->validate()) {
                                    $insertRows[] = $sourcesModel;
                                    $sourceCount++;
                                    $sourcesIds[$category][$message] = $sourceCount;
                                } else {
                                    echo var_export($sourcesModel->errors, true);
                                }
                            }
                        }
                    }
                }

                $sourcesModel = new Sources();
                $sql = $db->queryBuilder->batchInsert($sourcesModel::tableName(), $sourcesModel->attributes(), $insertRows);
                $db->createCommand($sql . ' ON DUPLICATE KEY UPDATE ' .
                    implode(', ',
                        array_map(function($attribute) {
                            return $attribute . ' = VALUES(' . $attribute . ')';
                        }, $sourcesModel->attributes())
                    )
                )->execute();

                echo "Added/updated sources: " . $sourceCount . "\n";

                $insertRows = [];
                $translationsCount = 0;
                if (is_countable($translationsList)) {
                    foreach ($translationsList as $lang => $sources) {
                        foreach ($sources as $category => $translations) {
                            foreach ($translations as $key => $translation) {

                                if (isset($sourcesIds[$category][$key])) {
                                    $id = $sourcesIds[$category][$key];
                                    $translationsModel = new Translations();
                                    $translationsModel->id = $id;
                                    $translationsModel->language = $lang;
                                    $translationsModel->translation = $translation;
                                    $translationsModel->status = 1;
                                    $translationsModel->created_at = new yii\db\Expression('NOW()');
                                    $translationsModel->created_by = 0;
                                    $translationsModel->updated_at = new yii\db\Expression('NOW()');
                                    $translationsModel->updated_by = 0;

                                    if ($translationsModel->validate()) {
                                        $insertRows[] = $translationsModel;
                                        $translationsCount++;
                                    } else {
                                        echo var_export($translationsModel->errors, true);
                                    }
                                }

                            }
                        }
                    }
                }

                $translationsModel = new Translations();
                $sql = $db->queryBuilder->batchInsert($translationsModel::tableName(), $translationsModel->attributes(), $insertRows);
                $db->createCommand($sql . ' ON DUPLICATE KEY UPDATE ' .
                    implode(', ',
                        array_map(function($attribute) {
                            return $attribute . ' = VALUES(' . $attribute . ')';
                        }, $translationsModel->attributes())
                    )
                )->execute();

                echo "Added/updated translations: " . $translationsCount . "\n";

            }
        } else if($selected == "4") {
            try {
                Yii::$app->db->createCommand()->checkIntegrity(false, '', Translations::tableName())->execute();
                Yii::$app->db->createCommand()->truncateTable(Translations::tableName())->execute();
                Yii::$app->db->createCommand()->checkIntegrity(true, '', Translations::tableName())->execute();
                Yii::$app->db->createCommand()->checkIntegrity(false, '', Sources::tableName())->execute();
                Yii::$app->db->createCommand()->truncateTable(Sources::tableName())->execute();
                Yii::$app->db->createCommand()->checkIntegrity(true, '', Sources::tableName())->execute();
                echo "All translations and sources has been successfully deleted.\n";
            } catch (\yii\db\Exception $exception)  {
                echo $this->ansiFormat("An error occurred while deleting translations and sources.\n\n", Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } else {
            echo $this->ansiFormat("Error! Your selection has not been recognized.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        echo "\n";
        return ExitCode::OK;
    }
}