<?php

use yii\db\Migration;

class m180314_143702_create_table_help extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%help}}', [
            'user_id' => $this->integer(11)->notNull(),
            'movie_id' => $this->integer(11)->notNull(),
            'rating' => $this->integer(11),
        ], $tableOptions);

        $this->addPrimaryKey('primary_key', '{{%help}}', ['user_id','movie_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%help}}');
    }
}
