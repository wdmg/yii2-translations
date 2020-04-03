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
