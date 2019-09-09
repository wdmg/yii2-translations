<?php

use yii\db\Migration;

/**
 * Class m190909_132054_langs
 */
class m190909_132054_langs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%trans_langs}}', [
            'id' => $this->primaryKey(), // Primary key ID (int)
            'url' => $this->string(3), // Language by ISO 639-1 / ISO 639-2, like `en` or `eng`
            'locale' => $this->string(10), // Language by RFC 3066, like `en-US`
            'name' => $this->string(64), // Title of language
            'is_default' => $this->tinyInteger(1)->null()->defaultValue(0), // Is default (source) language, 0 - no / 1 - yes
            'is_system' => $this->tinyInteger(1)->null()->defaultValue(0), // Is system language, 0 - no / 1 - yes
            'status' => $this->tinyInteger(1)->null()->defaultValue(0), // Status, 0 - draft / 1 - published
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'), // Source of translation created date (timestamp)
            'created_by' => $this->integer(11)->notNull()->defaultValue(0), // Source of translation created by user.id
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'), // Source of translation updated date (timestamp)
            'updated_by' => $this->integer(11)->notNull()->defaultValue(0), // Source of translation updated by user.id
        ], $tableOptions);

        $this->createIndex(
            '{{%idx-trans-langs}}',
            '{{%trans_langs}}',
            [
                'id',
                'url',
                'locale',
                'name',
            ]
        );

        // If exist module `Users` set index and foreign key `created_by`, `updated_by` to `users.id`
        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {

            $this->createIndex('{{%idx-trans-langs-author}}','{{%trans_langs}}', ['created_by', 'updated_by'],false);

            $userTable = \wdmg\users\models\Users::tableName();
            $this->addForeignKey(
                'fk_trans_langs_to_users',
                '{{%trans_langs}}',
                'created_by, updated_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropIndex('{{%idx-trans-langs}}', '{{%trans_langs}}');

        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {

            $this->dropIndex('{{%idx-trans-langs-author}}', '{{%trans_langs}}');

            $userTable = \wdmg\users\models\Users::tableName();
            if (!(Yii::$app->db->getTableSchema($userTable, true) === null)) {
                $this->dropForeignKey(
                    'fk_trans_langs_to_users',
                    '{{%trans_langs}}'
                );
            }
        }

        $this->truncateTable('{{%trans_langs}}');
        $this->dropTable('{{%trans_langs}}');
    }
}
