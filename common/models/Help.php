<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%help}}".
 *
 * @property int $user_id
 * @property int $movie_id
 * @property int $rating
 */
class Help extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%help}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'movie_id'], 'required'],
            [['user_id', 'movie_id', 'rating'], 'integer'],
            [['user_id', 'movie_id'], 'unique', 'targetAttribute' => ['user_id', 'movie_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'movie_id' => Yii::t('app', 'Movie ID'),
            'rating' => Yii::t('app', 'Rating'),
        ];
    }
}
