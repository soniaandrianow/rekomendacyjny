<?php
/**
 * Created by PhpStorm.
 * User: Sofia
 * Date: 16.03.2018
 * Time: 08:15
 */

namespace console\controllers;


use common\models\Help;
use common\models\Movie;
use common\models\Rating;
use common\models\User;
use MathPHP\Statistics\Average;
use MathPHP\Statistics\Descriptive;
use yii\console\Controller;

class CheckDataController extends Controller
{

    public function actionCheckAll()
    {
        $correct_count = 0;
        $not_correct_count = 0;
        $wrong_correct_count = 0;
        $wrong_not_correct_count = 0;
        $wrong_data = [];
        $allData = Help::findAll(['checked' => null]);
        foreach($allData as $data) {
            $correct = $this->oneDataCheck($data);
            if(!$correct) {
                if($data->correct == 0) {
                    $not_correct_count++;
                    $wrong_data [] = $data;
                } else {
                    $wrong_not_correct_count++;
                }
            } else {
                if($data->correct == 1) {
                    $correct_count++;
                } else {
                    $wrong_correct_count++;
                }
            }
        }
        var_dump('Wszystkie: ' . count($allData));
        var_dump('Poprawne: ' . $correct_count);
        var_dump('Niepoprawne: ' . $not_correct_count);
        var_dump('Poprawne uznane za błędne: ' . $wrong_not_correct_count);
        var_dump('Błędne uznane za poprawne: ' . $wrong_correct_count);


    }

    public function oneDataCheck(Help $data)
    {
       $correct_with_user = $this->checkWithUser($data);
       if(!$correct_with_user) {
           $correct_with_movie = $this->checkWithMovie($data);
           return $correct_with_movie;
       }
       return $correct_with_user;
    }

    public function checkWithUser(Help $data)
    {
        $genres_ratings = $this->createGenresVector($data);
        $mean = Average::mean($genres_ratings);
        $σ = Descriptive::standardDeviation($genres_ratings, true);
        $D = abs($data->rating - $mean);

        $correct = $D < 2 * $σ;

        return $correct;
    }

    public function checkWithMovie(Help $data)
    {
        $movie = Movie::find()->where(['id' => $data->movie_id])->one();
        $R = abs($data->rating - $movie->rating);
        $correct = $R < 0.3 * $movie->rating;

        return $correct;
    }

    public function createGenresVector(Help $data)
    {
        $genres_ratings = [];
        $movie = Movie::find()->where(['id' => $data->movie_id])->one();
        $user = User::find()->where(['id' => $data->user_id])->one();
        $genres = $movie->genres;
        foreach ($genres as $genre) {
            $rating = Rating::find()->where(['user_id' => $user->id])->andWhere(['genre_id' => $genre->id])->one();
            if($rating) {
                $genres_ratings[] = $rating->rating;
            }
        }
        return $genres_ratings;
    }

    public function actionRepareData()
    {

    }

}