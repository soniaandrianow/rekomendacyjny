<?php
/**
 * Created by PhpStorm.
 * User: Sofia
 * Date: 11.03.2018
 * Time: 14:02
 */

namespace console\controllers;


use Phpml\Classification\NaiveBayes;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;
use yii\console\Controller;

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
        $samples = [[1.5, 2.1, 1.2], [2.3, 3.0, 1.0], [4.3, 3.8, 5.1]];
        $labels = ['1', '2', '4'];

        $classifier = new SVC(Kernel::RBF, $cost = 1000, $degree = 3, $gamma = 6);
        $classifier->train($samples, $labels);

        $prediction = $classifier->predict([2.2, 4.5, 5.0]);
        var_dump($prediction); die;
    }

}