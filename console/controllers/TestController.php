<?php
/**
 * Created by PhpStorm.
 * User: Sofia
 * Date: 11.03.2018
 * Time: 14:02
 */

namespace console\controllers;


use common\models\Help;
use common\models\Movie;
use common\models\Rating;
use MathPHP\Statistics\Average;
use Phpml\Classification\NaiveBayes;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use Yii;
use yii\console\Controller;
use yii\db\Expression;

class TestController extends Controller
{
    public function actionBayes()
    {
        $samples = [[1.5, 2.1, 1.2], [2.3, 3.0, 1.0], [4.3, 3.8, 5.1]];
        $labels = ['1', '2', '4'];

        $classifier = new NaiveBayes();
        $classifier->train($samples, $labels);

        $prediction = $classifier->predict([2.2, 4.5, 5.0]);
        var_dump($prediction); die;
    }

    public function actionSvc()
    {
        $data = $this->prepareSamples();
        $samples = $data['samples'];
        $labels = $data['labels'];

        $classifier = new SVC(Kernel::RBF, $cost = 1000, $degree = 3, $gamma = 6);
        $classifier->train($samples, $labels);

        $prediction = $classifier->predict([4, 2.2, 4.3]);
        var_dump($prediction); die;
    }

    public function prepareSamples()
    {
        $samples = [];
        $labels = [];
        $helps = Help::find()->orderBy(new Expression('rand()'))->limit(200)->all();
        foreach($helps as $help) {
            $movie = Movie::findOne(['id' => $help->movie_id]);
            $movie_rating = $movie->rating;
            $genre_ratings = [];
            foreach($movie->genres as $genre) {
                $rating = Rating::findOne(['and', ['user_id' => $help->user_id], ['genre_id' => $genre->id]])->rating;
                $genre_ratings[] = $rating;
            }
            $genre_rating = Average::median($genre_ratings);
            $samples[] = [$help->rating, $movie_rating, $genre_rating];
            $labels[] = $help->rating;
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