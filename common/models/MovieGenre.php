<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%movie_genre}}".
 *
 * @property int $movie_id
 * @property int $genre_id
 *
 * @property Genre $genre
 * @property Movie $movie
 */
class MovieGenre extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%movie_genre}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['movie_id', 'genre_id'], 'required'],
            [['movie_id', 'genre_id'], 'integer'],
            [['movie_id', 'genre_id'], 'unique', 'targetAttribute' => ['movie_id', 'genre_id']],
            [['genre_id'], 'exist', 'skipOnError' => true, 'targetClass' => Genre::className(), 'targetAttribute' => ['genre_id' => 'id']],
            [['movie_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movie::className(), 'targetAttribute' => ['movie_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'movie_id' => Yii::t('app', 'Movie ID'),
            'genre_id' => Yii::t('app', 'Genre ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGenre()
    {
        return $this->hasOne(Genre::className(), ['id' => 'genre_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMovie()
    {
        return $this->hasOne(Movie::className(), ['id' => 'movie_id']);
    }
}
