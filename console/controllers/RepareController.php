<?php
/**
 * Created by PhpStorm.
 * User: Sofia
 * Date: 16.03.2018
 * Time: 10:03
 */

namespace console\controllers;


use common\models\Help;
use common\models\Movie;
use common\models\Rating;
use common\models\User;
use MathPHP\Statistics\Average;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use Yii;
use yii\console\Controller;
use yii\db\Expression;

class RepareController extends Controller
{

    public function actionRepare($data)
    {
        $fixed_array = [];
        $svm = $this->actionTrainSvm();
        foreach($data as $d) {
            $fixed = $this->repareOne($data, $svm);
            $fixed_array [] = $fixed;
        }
        var_dump($fixed_array);
    }

    public function repareOne($data, SVC $svm)
    {
        $fixed = $svm->predict($data);
        $data->rating = $fixed;
        $data->checked = 1;
        $data->update(false, ['rating', 'checked']);
        $movies = Movie::find()->all();

        foreach ($movies as $movie) {
            Yii::$app->runAction('file/count-movie-ratings', ['movie' => $movie]);
            //$this->countMovieRatings($movie);
        }

        $users = User::find()->all();
        foreach($users as $user) {
            Yii::$app->runAction('file/count-genre-ratings', ['user' => $user]);
            //$this->createRatingsForUser($user);
        }
        var_dump('Poprawiono, przeliczono (y)');
        return $fixed;
    }

    public function actionTrainSvm()
    {
        $data = $this->prepareSamples();
//        $samples = $data['samples'];
//        $labels = $data['labels'];
//
//        $classifier = new SVC(Kernel::RBF, $cost = 100000, $gamma = 100);
//        $classifier->train($samples, $labels);
//
//        return $classifier;
    }

    public function prepareSamples()
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
        $fileName = '/train.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/labels.txt';
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