<?php

use yii\db\Migration;

class m180309_131632_create_table_rating extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%rating}}', [
            'user_id' => $this->integer(11)->notNull(),
            'genre_id' => $this->integer(11)->notNull(),
            'rating' => $this->decimal(2,1),
        ], $tableOptions);

        $this->addPrimaryKey('primary_key', '{{%rating}}', ['user_id','genre_id']);

        $this->addForeignKey('genre_id_fk_rating', '{{%rating}}', 'genre_id', '{{%genre}}', 'id');
        $this->addForeignKey('user_id_fk_rating', '{{%rating}}', 'user_id', '{{%user}}', 'id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%rating}}');
    }
}
