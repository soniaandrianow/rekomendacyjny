<?php

use yii\db\Migration;

class m180309_131612_create_table_movie extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%movie}}', [
            'id' => $this->integer(11)->notNull()->append('PRIMARY KEY'),
            'name' => $this->string(45),
            'rating' => $this->decimal(2,1),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%movie}}');
    }
}
