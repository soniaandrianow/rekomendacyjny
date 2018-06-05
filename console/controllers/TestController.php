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

        $prediction = $classifier->predict([2.2, 5.0]);
        var_dump($prediction); die;
    }

    public function actionSvc()
    {
        $data = $this->prepareSamples();
        var_dump('utworzono pliki');
        $samples = $data['samples'];
        $labels = $data['labels'];

//        $classifier = new SVC(Kernel::RBF, $cost = 100000, $gamma = 100);
//        $classifier->train($samples, $labels);
//
//        $prediction = $classifier->predict([2.2, 4.3]);
//        var_dump($prediction); die;
    }

    public function prepareSamples()
    {
        $skip = false;
        $samples = [];
        $labels = [];
        $helps = Help::find()->where(['checked' => 1])->orderBy(new Expression('rand()'))->limit(5000)->all();
        var_dump(count($helps));
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
                $new_one = [$movie_rating, $genre_rating];
                //shuffle($new_one);
                $samples[] = $new_one;
                $labels[] = $help->rating;
            }
            $skip = false;
        }
        var_dump($samples);
        var_dump($labels);
        $path = Yii::getAlias('@console') . '/files';
        $fileName = '/train-d.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/labels-d.txt';
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

    public function actionSaveForScatter()
    {
        $skip = false;
        $samples_0_5 = [];
        $samples_1= [];
        $samples_1_5= [];
        $samples_2= [];
        $samples_2_5= [];
        $samples_3= [];
        $samples_3_5= [];
        $samples_4= [];
        $samples_4_5= [];
        $samples_5= [];
        $labels = [];
        $helps = Help::find()->orderBy(new Expression('rand()'))->limit(500)->all();
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
            if (!$skip) {
                var_dump($help->rating);
                $genre_rating = Average::median($genre_ratings);
                switch ($help->rating) {
                    case 0.5:
                        $samples_0_5[] = [$movie_rating, $genre_rating];
                        break;
                    case 1.0:
                        $samples_1[] = [$movie_rating, $genre_rating];
                        break;
                    case 1.5:
                        $samples_1_5[] = [$movie_rating, $genre_rating];
                        break;
                    case 2.0:
                        $samples_2[] = [$movie_rating, $genre_rating];
                        break;
                    case 2.5:
                        $samples_2_5[] = [$movie_rating, $genre_rating];
                        break;
                    case 3.0:
                        $samples_3[] = [$movie_rating, $genre_rating];
                        break;
                    case 3.5:
                        $samples_3_5[] = [$movie_rating, $genre_rating];
                        break;
                    case 4.0:
                        $samples_4[] = [$movie_rating, $genre_rating];
                        break;
                    case 4.5:
                        $samples_4_5[] = [$movie_rating, $genre_rating];
                        break;
                    case 5.0:
                        $samples_5[] = [$movie_rating, $genre_rating];
                        break;
                }
            }
            $skip = false;
        }
        $path = Yii::getAlias('@console') . '/files';
        $fileName = '/scatter05.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_0_5 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter1.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_1 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter15.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_1_5 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter2.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_2 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter25.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_2_5 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter3.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_3 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter35.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_3_5 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter4.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_4 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter45.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_4_5 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);

        $fileName = '/scatter5.txt';
        $file = fopen($path . $fileName, 'wb');
        foreach($samples_5 as $sample)
        {
            foreach($sample as $data) {
                fwrite($file, $data . '; ');
            }
            fwrite($file, PHP_EOL);
        }
        fclose($file);
    }

}