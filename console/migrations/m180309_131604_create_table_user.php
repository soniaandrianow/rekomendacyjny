<?php

use yii\db\Migration;

class m180309_131604_create_table_user extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->integer(11)->notNull()->append('PRIMARY KEY'),
            'name' => $this->string(45),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
