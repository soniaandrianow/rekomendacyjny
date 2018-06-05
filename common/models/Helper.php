<?php
/**
 * Created by PhpStorm.
 * User: Sofia
 * Date: 16.03.2018
 * Time: 14:00
 */

namespace common\models;


use MathPHP\Statistics\Average;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use Yii;
use yii\base\Model;
use yii\db\Expression;

class Helper extends Model
{
    public static function prepareVector($data)
    {
        $vector = null;
        $skip = false;
        $movie = Movie::findOne(['id' => $data->movie_id]);
        $movie_rating = $movie->rating;
        $genre_ratings = [];
        foreach($movie->genres as $genre) {
            $ratingModel = Rating::find()->where(['user_id' => $data->user_id])->andWhere(['genre_id' => $genre->id])->one();
            if($ratingModel) {
                $rating = $ratingModel->rating;
                $genre_ratings[] = $rating;
            } else {
                $skip = true;
                break;
            }
        }
        if(!$skip) {
            $genre_rating = Average::median($genre_ratings);
            $vector = [$data->rating, $movie_rating, $genre_rating];
        }
        return $vector;

    }

    public static function repareOne($data, SVC $svm)
    {
        $vector = Helper::prepareVector($data);
        if($vector) {
            $fixed = $svm->predict($vector);
            $data->rating = $fixed;
            $data->checked = 1;
            $data->update(false, ['rating', 'checked']);
//            $movies = Movie::find()->all();

//            foreach ($movies as $movie) {
//                static::countMovieRatings($movie);
//                //$this->countMovieRatings($movie);
//            }
//
//            $users = User::find()->all();
//            foreach($users as $user) {
//                static::countGenreRating($user);
//                //$this->createRatingsForUser($user);
//            }
            //var_dump('Poprawiono, przeliczono (y)');
            return $fixed;
        }
        return null;
    }

    public static function countMovieRatings($movie)
    {
        var_dump($movie->id . ' ' . $movie->name);
        $helps = Help::find()->where(['movie_id' => $movie->id])->andWhere(['checked' => 1])->all();
        var_dump(count($helps));
        $sum = 0;
        $counter = 0;
        foreach ($helps as $help) {
            $sum += $help->rating;
            $counter +=1;
        }
        var_dump($sum . ' / ' . $counter);
        if($counter != 0) {
            $mr = $sum / $counter;
        } else {
            $mr = 3.5;
        }
        $movie->rating = $mr;
        $movie->update(true, ['rating']);
    }
    public static function countGenreRating($user, $genre, $rating_val)
    {
        $rating = Rating::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['genre_id' => $genre->id])
            ->one();
        if ($rating) {
            $rating->rating = ($rating->rating + $rating_val) / 2;
        } else {
            $rating = new Rating();
            $rating->user_id = $user->id;
            $rating->genre_id = $genre->id;
            $rating->rating = $rating_val;
        }
        $rating->save();
    }


    public static function trainSvm()
    {
        $data = static::prepareSamples();
//        $samples = $data['samples'];
//        $labels = $data['labels'];
//
//        $classifier = new SVC(Kernel::RBF, $cost = 100000, $gamma = 100);
//        $classifier->train($samples, $labels);
//
//        return $classifier;
    }

    public static function prepareSamples()
    {
        $skip = false;
        $samples = [];
        $labels = [];
        $helps = Help::find()->where(['checked' => 1])->orderBy(new Expression('rand()'))->all();
        foreach($helps as $help) {
            $movie = Movie::findOne(['id' => $help->movie_id]);
            $movie_rating = $movie->rating;
            $genre_ratings = [];
            foreach($movie->genres as $genre) {
                $ratingModel = Rating::find()->where(['user_id' => $help->user_id])->andWhere(['genre_id' => $genre->id])->one();
                if($ratingModel) {
                    $rating = $ratingModel->rating;
                    $genre_ratings[] = $rating;
                } else {
                    $skip = true;
                    break;
                }
            }
            if(!$skip) {
                $genre_rating = Average::median($genre_ratings);
                $samples[] = [$help->rating, $movie_rating, $genre_rating];
                $labels[] = $help->rating;
            }
            $skip = false;
        }
        $path = Yii::getAlias('@console') . '/files';
        $fileName = '/train-r.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/labels-r.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($labels as $label)
        {
            fwrite($file, $label . PHP_EOL);
        }
        fclose($file);
        return [
            'samples' => $samples,
            'labels' => $labels
        ];

    }
}