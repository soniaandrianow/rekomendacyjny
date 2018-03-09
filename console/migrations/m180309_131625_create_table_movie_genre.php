<?php

use yii\db\Migration;

class m180309_131625_create_table_movie_genre extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%movie_genre}}', [
            'movie_id' => $this->integer(11)->notNull(),
            'genre_id' => $this->integer(11)->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('primary_key', '{{%movie_genre}}', ['movie_id','genre_id']);

        $this->addForeignKey('genre_id_fk', '{{%movie_genre}}', 'genre_id', '{{%genre}}', 'id');
        $this->addForeignKey('movie_id_fk', '{{%movie_genre}}', 'movie_id', '{{%movie}}', 'id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%movie_genre}}');
    }
}
