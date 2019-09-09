<?php

use yii\db\Migration;

/**
 * Class m190909_132533_translations
 */
class m190909_132533_translations extends Migration
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

        $this->createTable('{{%trans_sources}}', [
            'id' => $this->primaryKey(), // Primary key ID (int)
            'category' => $this->string(255), // Category of translation
            'alias' => $this->string(32), // Alias key of translation, like `is-text-about-company-12945f0845` for entry `Is text about company...`
            'message' => $this->text(), // Message source
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'), // Source of translation created date (timestamp)
            'created_by' => $this->integer(11)->notNull()->defaultValue(0), // Source of translation created by user.id
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'), // Source of translation updated date (timestamp)
            'updated_by' => $this->integer(11)->notNull()->defaultValue(0), // Source of translation updated by user.id
        ], $tableOptions);

        $this->createIndex(
            '{{%idx-trans-sources}}',
            '{{%trans_sources}}',
            [
                'id',
                'category',
                'alias',
                'message(255)'
            ]
        );

        $this->createTable('{{%trans_messages}}', [
            'id' => $this->integer(11)->notNull(), // Primary key ID (int) of `trans_sources.id`
            'language' => $this->string(16), // Lang ID
            'translation' => $this->text(), // Message translation
            'status' => $this->tinyInteger(1)->null()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'), // Translation created date (timestamp)
            'created_by' => $this->integer(11)->notNull()->defaultValue(0), // Translation created by user.id
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'), // Translation updated date (timestamp)
            'updated_by' => $this->integer(11)->notNull()->defaultValue(0), // Translation updated by user.id
        ], $tableOptions);

        $this->createIndex(
            '{{%idx-trans-messages}}',
            '{{%trans_messages}}',
            [
                'id',
                'language',
                'translation(255)',
                'status'
            ]
        );

        $this->addForeignKey(
            'fk_trans_messages_to_source',
            '{{%trans_messages}}',
            'id',
            '{{%trans_sources}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // If exist module `Users` set index and foreign key `created_by`, `updated_by` to `users.id`
        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {

            $this->createIndex('{{%idx-trans-sources-author}}','{{%trans_sources}}', ['created_by', 'updated_by'],false);
            $this->createIndex('{{%idx-trans-sources-messages}}','{{%trans_messages}}', ['created_by', 'updated_by'],false);

            $userTable = \wdmg\users\models\Users::tableName();
            $this->addForeignKey(
                'fk_trans_sources_to_users',
                '{{%trans_sources}}',
                'created_by, updated_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_trans_messages_to_users',
                '{{%trans_messages}}',
                'created_by, updated_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
        }

        /*if(class_exists('\wdmg\translations\models\Languages')) {
            $this->addForeignKey(
                'fk_trans_messages_to_langs',
                '{{%trans_messages}}',
                'language',
                '{{%trans_langs}}',
                'locale',
                'NO ACTION',
                'CASCADE'
            );
        }*/

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {

            $this->dropIndex('{{%idx-trans-sources-author}}', '{{%trans_sources}}');
            $this->dropIndex('{{%idx-trans-messages-author}}', '{{%trans_messages}}');

            $userTable = \wdmg\users\models\Users::tableName();
            if (!(Yii::$app->db->getTableSchema($userTable, true) === null)) {
                $this->dropForeignKey(
                    'fk_trans_sources_to_users',
                    '{{%trans_sources}}'
                );
                $this->dropForeignKey(
                    'fk_trans_messages_to_users',
                    '{{%trans_messages}}'
                );
            }

            /*if(class_exists('\wdmg\translations\models\Languages')) {
                $this->dropForeignKey(
                    'fk_trans_messages_to_langs',
                    '{{%trans_messages}}'
                );
            }*/
        }

        $this->dropForeignKey(
            'fk_trans_messages_to_source',
            '{{%trans_messages}}'
        );


        $this->truncateTable('{{%trans_messages}}');
        $this->dropTable('{{%trans_messages}}');

        $this->truncateTable('{{%trans_sources}}');
        $this->dropTable('{{%trans_sources}}');
    }
}
