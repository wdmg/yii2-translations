<?php

use yii\db\Migration;

/**
 * Class m200401_132436_langs_frontend
 */
class m200401_132436_langs_frontend extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%trans_langs}}')->getColumn('is_frontend'))) {
            $this->addColumn('{{%trans_langs}}', 'is_frontend', $this->tinyInteger(1)->null()->defaultValue(0)->after('is_system'));
            $this->createIndex('{{%idx-trans-langs-frontend}}', '{{%trans_langs}}', ['is_frontend']);
        }

        $this->createIndex('{{%idx-trans-langs-locale}}', '{{%trans_langs}}', ['locale']);

        // Add default language
        if (isset(Yii::$app->sourceLanguage)) {
            $module = \wdmg\translations\Module::class;
            $locale = $module::parseLocale(Yii::$app->sourceLanguage, Yii::$app->language);
            if ($locale) {
                $this->insert('{{%trans_langs}}', [
                    'url' => $locale['short'],
                    'locale' => $locale['locale'],
                    'name' => $locale['name'],
                    'is_default' => 1,
                    'is_system' => 1,
                    'status' => 1
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%trans_langs}}')->getColumn('is_frontend'))) {
            $this->dropIndex('{{%idx-trans-langs-frontend}}', '{{%trans_langs}}');
            $this->dropColumn('{{%trans_langs}}', 'is_frontend');
        }

        $this->dropIndex('{{%idx-trans-langs-locale}}', '{{%trans_langs}}');
    }
}
