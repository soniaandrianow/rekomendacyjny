<?php
/**
 * Created by PhpStorm.
 * User: Sofia
 * Date: 09.03.2018
 * Time: 14:46
 */

namespace console\controllers;


use common\models\Genre;
use common\models\Help;
use common\models\Helper;
use common\models\Movie;
use common\models\MovieGenre;
use common\models\Rating;
use common\models\User;
use Yii;
use yii\console\Controller;
use yii\db\Expression;

class FileController extends Controller
{
    public function actionReadInData($counter)
    {
        //$file_directory = "C:\Users\Sofia\Desktop\Praca_Magisterska\small";
        $file_directory = 'C:\Users\Sofia\Desktop\Praca_Magisterska\real';
        //$this->readInGenres($file_directory);

        var_dump('genres in db');

        //$this->readInUsers($file_directory);
        //$this->readInRealUsers($file_directory);
        var_dump('users in db');

        //$this->readInRealMovies($file_directory);
        //$this->readInMovies($file_directory);

        var_dump('movies in db');
//
        //$this->readInRealRatings($file_directory);
        //$this->readInHelps($file_directory);
        //$this->actionCountCheck();
//
        var_dump('helps in db');

        $movies = Movie::find()->all();
        foreach ($movies as $movie) {
            $this->countMovieRatings($movie, $counter);
        }

        var_dump('for movies ok');

        $users = User::find()->all();
        foreach($users as $user) {
            $this->createRatingsForUser($user, $counter);
        }
    }

    public function actionRecountStatus($count)
    {
        $movies = Movie::find()->all();
        foreach ($movies as $movie) {
            $this->countMovieRatings($movie, $count);
        }

        var_dump('for movies ok');

        $users = User::find()->all();
        foreach($users as $user) {
            $this->createRatingsForUser($user, $count);
        }
    }

    public function createRatingsForUser($user, $count)
    {
        $helps = Help::find()->where(['user_id' => $user->id])->andWhere(['<>', 'checked', $count])->all();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($helps as $help) {
                $movie = Movie::findOne(['id' => $help->movie_id]);
                $user = User::findOne(['id' => $help->user_id]);
                $rating_val = $help->rating;
                $genres = $movie->genres;
                foreach ($genres as $genre) {
                    $this->countGenreRating($user, $genre, $rating_val);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            var_dump($e->getMessage()); die;
        }
    }

    public function readInRealUsers($file_directory)
    {
        //$fil = fopen ($file_directory . "\users.dat", "r");
        $fil = fopen($file_directory . '\users.txt', "r");
        //fgetcsv($fil);
        $user_ex = new User();
        $users = [];
        while ($filedata = fgets($fil)) {
            $q = explode(';', $filedata);
            if (!User::findOne(['id' => $q[0]])) {
                $user = new User();
                $user->id = $q[0];
                $user->name = 'User' . $q[0];
                $users[] = $user;
            }
        }
        $command = \Yii::$app->db->createCommand()->batchInsert(User::tableName(), $user_ex->attributes(), $users);
        $sql = $command->getRawSql();
        $sql .= ' ON DUPLICATE KEY UPDATE id = id';
        $command->setRawSql($sql);
        $command->execute();
    }

    public function readInUsers($file_directory)
    {
        //$fil = fopen ($file_directory . "\users.dat", "r");
        $fil = fopen($file_directory . '\ratings.csv', "r");
        fgetcsv($fil);
        $user_ex = new User();
        $users = [];
        while ($filedata = fgets($fil)) {
            $q = explode(',', $filedata);
            if (!User::findOne(['id' => $q[0]])) {
                $user = new User();
                $user->id = $q[0];
                $user->name = 'User' . $q[0];
                $users[] = $user;
            }
        }
        //$db = Yii::$app->db;
        //$sql = $db->queryBuilder->batchInsert(User::tableName(), $user_ex->attributes(), $users);
        //var_dump($sql); die;

        $command = \Yii::$app->db->createCommand()->batchInsert(User::tableName(), $user_ex->attributes(), $users);
        $sql = $command->getRawSql();
        $sql .= ' ON DUPLICATE KEY UPDATE id = id';
        $command->setRawSql($sql);
        $command->execute();

        //$db->createCommand($sql . ' ON DUPLICATE KEY UPDATE')->execute();
        //Yii::$app->db->createCommand()->batchInsert(User::tableName(), $user_ex->attributes(), $users)->execute();
    }

    public function readInGenres($file_directory)
    {
        $fil = fopen ($file_directory . "\genres.txt", "r");
        $genre_ex = new Genre();
        $genres = [];
        while ($filedata = fgets($fil))
        {
            $genre = new Genre();
            $genre->name = trim($filedata);
            $genres[] = $genre;
        }
        Yii::$app->db->createCommand()->batchInsert(Genre::tableName(), $genre_ex->attributes(), $genres)->execute();
    }

    public function readInRealMovies($file_directory)
    {
        //$fil = fopen ($file_directory . "\movies.dat", "r");
        $fil = fopen ($file_directory . "\movies.txt", "r");

        $movie_ex = new Movie();
        $movie_genre_ex = new MovieGenre();
        $movies = [];
        $movie_genres = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            while ($filedata = fgets($fil)) {
                //$q=explode('::', $filedata);
                $q = explode(';', $filedata);
                $movie = new Movie();
                $movie->id = $q[0];
                $name = static::normalizeChars($q[1]);
//                if ((substr($name, 0, 1) == '"')) {
//                    //var_dump('reszta: ' . $q[2]);
//                    $rest = explode('"', $filedata);
//                    var_dump($filedata);
//                    var_dump($rest);
//                    $movie->name = $rest[1];
//                    $genres = explode('|', str_replace(',', '', $rest[2]));
//                } else {
                    $movie->name = $name;
                    $genres = explode('|', $q[2]);
                //}
                foreach ($genres as $genre) {
                    $mg = new MovieGenre();
                    $mg->movie_id = $movie->id;
                    var_dump($movie->name . ' ' . trim($genre));
                    $mg->genre_id = Genre::findOne(['name' => ucfirst(strtolower(str_replace(' ', '', trim($genre))))])->id;
                    $movie_genres[] = $mg;
                }
                $movies[] = $movie;
                if (count($movies) == 500) {
                    Yii::$app->db->createCommand()->batchInsert(Movie::tableName(), $movie_ex->attributes(), $movies)->execute();
                    unset($movies);
                    $movies = [];
                    Yii::$app->db->createCommand()->batchInsert(MovieGenre::tableName(), $movie_genre_ex->attributes(), $movie_genres)->execute();
                    unset($movie_genres);
                    $movie_genres = [];
                }
            }
            Yii::$app->db->createCommand()->batchInsert(Movie::tableName(), $movie_ex->attributes(), $movies)->execute();
            Yii::$app->db->createCommand()->batchInsert(MovieGenre::tableName(), $movie_genre_ex->attributes(), $movie_genres)->execute();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            var_dump($e->getMessage()); die;
        }
    }

    public function readInMovies($file_directory)
    {
        //$fil = fopen ($file_directory . "\movies.dat", "r");
        $fil = fopen ($file_directory . "\movies.csv", "r");
        fgetcsv($fil);
        $movie_ex = new Movie();
        $movie_genre_ex = new MovieGenre();
        $movies = [];
        $movie_genres = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            while ($filedata = fgets($fil)) {
                //$q=explode('::', $filedata);
                $q = explode(',', $filedata);
                $movie = new Movie();
                $movie->id = $q[0];
                $name = static::normalizeChars($q[1]);
                if ((substr($name, 0, 1) == '"')) {
                    //var_dump('reszta: ' . $q[2]);
                    $rest = explode('"', $filedata);
                    var_dump($filedata);
                    var_dump($rest);
                    $movie->name = $rest[1];
                    $genres = explode('|', str_replace(',', '', $rest[2]));
                } else {
                    $movie->name = $name;
                    $genres = explode('|', $q[2]);
                }
                foreach ($genres as $genre) {
                    $mg = new MovieGenre();
                    $mg->movie_id = $movie->id;
                    var_dump($movie->name . ' ' . trim($genre));
                    $mg->genre_id = Genre::findOne(['name' => trim($genre)])->id;
                    $movie_genres[] = $mg;
                }
                $movies[] = $movie;
                if (count($movies) == 500) {
                    Yii::$app->db->createCommand()->batchInsert(Movie::tableName(), $movie_ex->attributes(), $movies)->execute();
                    unset($movies);
                    $movies = [];
                    Yii::$app->db->createCommand()->batchInsert(MovieGenre::tableName(), $movie_genre_ex->attributes(), $movie_genres)->execute();
                    unset($movie_genres);
                    $movie_genres = [];
                }
            }
            Yii::$app->db->createCommand()->batchInsert(Movie::tableName(), $movie_ex->attributes(), $movies)->execute();
            Yii::$app->db->createCommand()->batchInsert(MovieGenre::tableName(), $movie_genre_ex->attributes(), $movie_genres)->execute();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            var_dump($e->getMessage()); die;
        }
    }

    public function readInRealRatings($file_directory)
    {
        $fil = fopen($file_directory . '\oceny.txt', "r");
        $array = explode("\n", fread($fil, filesize($file_directory . '\oceny.txt')));
        shuffle($array);
        $help_ex = new Help();
        $helps = [];
        $counter = 0;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($array as $filedata) {
                var_dump($filedata);
                if (strlen($filedata) > 0) {
//            $q = explode('::', $filedata);
                    $q = explode(';', $filedata);
                    $help = $this->createNewHelp($q, $counter);
//                    if($help->checked) {
//                        $counter++;
//                    }
                    $helps[] = $help;
                }
                if (count($helps) == 100) {
                    Yii::$app->db->createCommand()->batchInsert(Help::tableName(), $help_ex->attributes(), $helps)->execute();
                    unset($helps);
                    $helps = [];
                }
            }
            Yii::$app->db->createCommand()->batchInsert(Help::tableName(), $help_ex->attributes(), $helps)->execute();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            var_dump($e->getMessage());
            die;
        }
    }

    public function readInHelps($file_directory)
    {
        //$fil = fopen($file_directory . '\ratings.dat', "r");
        $fil = fopen($file_directory . '\ratings.csv', "r");
        fgetcsv($fil);
        $array = explode("\n", fread($fil, filesize($file_directory . '\ratings.csv')));
        shuffle($array);
        // $filed = shuffle($filed);
        $help_ex = new Help();
        $helps = [];
        $counter = 0;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($array as $filedata) {
                var_dump($filedata);
                if (strlen($filedata) > 0) {
//            $q = explode('::', $filedata);
                    $q = explode(',', $filedata);
                    $help = $this->createNewHelp($q, $counter);
                    if($help->checked) {
                        $counter++;
                    }
                    $helps[] = $help;
                }
                if (count($helps) == 500) {
                    Yii::$app->db->createCommand()->batchInsert(Help::tableName(), $help_ex->attributes(), $helps)->execute();
                    unset($helps);
                    $helps = [];
                }
            }
            Yii::$app->db->createCommand()->batchInsert(Help::tableName(), $help_ex->attributes(), $helps)->execute();
            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            var_dump($e->getMessage());
            die;
        }
    }

    public function actionCountCheck()
    {
//        $checked = Help::find()->where(['checked' => 1])->all();
//        var_dump(count($checked));
//        if(count($checked) < 1397) {
//            $diff = 1397 - count($checked);
//            var_dump('róźnica: ' . $diff);
//            $to_check = Help::find()->where(['checked' => null])->orderBy(new Expression('rand()'))->limit($diff)->all();
//            var_dump('wybrano do spawdzenia');
//            foreach ($to_check as $data) {
//                $data->checked = 1;
//                $data->save('false', ['checked']);
//            }
//        }
        $diff = 349;
        for ($i = 0; $i < 5; $i++) {
            $to_check = Help::find()->where(['checked' => null])->orderBy(new Expression('rand()'))->limit($diff)->all();
            var_dump('wybrano do spawdzenia ' . ($i + 1));
            foreach ($to_check as $data) {
                $data->checked = ($i+1);
                $data->save('false', ['checked']);
           }
        }

    }

    public function countMovieRatings($movie, $count)
{
    var_dump($movie->id . ' ' . $movie->name);
    $helps = Help::find()->where(['movie_id' => $movie->id])->andWhere(['<>', 'checked', $count])->all();
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
    public function countGenreRating($user, $genre, $rating_val)
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

    public function createNewHelp($q, $counter)
    {
        $help = new Help();
        $help->movie_id = (int)$q[1];
        $help->user_id = (int)$q[0];
        $help->rating = (int)$q[2];
        $help->correct = 1;
        $help->original = (int)$q[2];
        //$checked = rand(0,1);
        //$help->checked = 0;
//        if($checked && $counter <= 80000){
//            $help->checked = 1;
//        }
        return $help;
    }

    /**
     * Replace language-specific characters by ASCII-equivalents.
     * @param string $s
     * @return string
     */
    public static function normalizeChars($s)
    {
        $replace = array(
            'ъ' => '-', 'Ь' => '-', 'Ъ' => '-', 'ь' => '-',
            'Ă' => 'A', 'Ą' => 'A', 'À' => 'A', 'Ã' => 'A', 'Á' => 'A', 'Æ' => 'A', 'Â' => 'A', 'Å' => 'A', 'Ä' => 'Ae',
            'Þ' => 'B',
            'Ć' => 'C', 'ץ' => 'C', 'Ç' => 'C',
            'È' => 'E', 'Ę' => 'E', 'É' => 'E', 'Ë' => 'E', 'Ê' => 'E',
            'Ğ' => 'G',
            'İ' => 'I', 'Ï' => 'I', 'Î' => 'I', 'Í' => 'I', 'Ì' => 'I',
            'Ł' => 'L',
            'Ñ' => 'N', 'Ń' => 'N',
            'Ø' => 'O', 'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'Oe',
            'Ş' => 'S', 'Ś' => 'S', 'Ș' => 'S', 'Š' => 'S',
            'Ț' => 'T',
            'Ù' => 'U', 'Û' => 'U', 'Ú' => 'U', 'Ü' => 'Ue',
            'Ý' => 'Y',
            'Ź' => 'Z', 'Ž' => 'Z', 'Ż' => 'Z',
            'â' => 'a', 'ǎ' => 'a', 'ą' => 'a', 'á' => 'a', 'ă' => 'a', 'ã' => 'a', 'Ǎ' => 'a', 'а' => 'a', 'А' => 'a', 'å' => 'a', 'à' => 'a', 'א' => 'a', 'Ǻ' => 'a', 'Ā' => 'a', 'ǻ' => 'a', 'ā' => 'a', 'ä' => 'ae', 'æ' => 'ae', 'Ǽ' => 'ae', 'ǽ' => 'ae',
            'б' => 'b', 'ב' => 'b', 'Б' => 'b', 'þ' => 'b',
            'ĉ' => 'c', 'Ĉ' => 'c', 'Ċ' => 'c', 'ć' => 'c', 'ç' => 'c', 'ц' => 'c', 'צ' => 'c', 'ċ' => 'c', 'Ц' => 'c', 'Č' => 'c', 'č' => 'c', 'Ч' => 'ch', 'ч' => 'ch',
            'ד' => 'd', 'ď' => 'd', 'Đ' => 'd', 'Ď' => 'd', 'đ' => 'd', 'д' => 'd', 'Д' => 'D', 'ð' => 'd',
            'є' => 'e', 'ע' => 'e', 'е' => 'e', 'Е' => 'e', 'Ə' => 'e', 'ę' => 'e', 'ĕ' => 'e', 'ē' => 'e', 'Ē' => 'e', 'Ė' => 'e', 'ė' => 'e', 'ě' => 'e', 'Ě' => 'e', 'Є' => 'e', 'Ĕ' => 'e', 'ê' => 'e', 'ə' => 'e', 'è' => 'e', 'ë' => 'e', 'é' => 'e',
            'ф' => 'f', 'ƒ' => 'f', 'Ф' => 'f',
            'ġ' => 'g', 'Ģ' => 'g', 'Ġ' => 'g', 'Ĝ' => 'g', 'Г' => 'g', 'г' => 'g', 'ĝ' => 'g', 'ğ' => 'g', 'ג' => 'g', 'Ґ' => 'g', 'ґ' => 'g', 'ģ' => 'g',
            'ח' => 'h', 'ħ' => 'h', 'Х' => 'h', 'Ħ' => 'h', 'Ĥ' => 'h', 'ĥ' => 'h', 'х' => 'h', 'ה' => 'h',
            'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ì' => 'i', 'į' => 'i', 'ĭ' => 'i', 'ı' => 'i', 'Ĭ' => 'i', 'И' => 'i', 'ĩ' => 'i', 'ǐ' => 'i', 'Ĩ' => 'i', 'Ǐ' => 'i', 'и' => 'i', 'Į' => 'i', 'י' => 'i', 'Ї' => 'i', 'Ī' => 'i', 'І' => 'i', 'ї' => 'i', 'і' => 'i', 'ī' => 'i', 'ĳ' => 'ij', 'Ĳ' => 'ij',
            'й' => 'j', 'Й' => 'j', 'Ĵ' => 'j', 'ĵ' => 'j', 'я' => 'ja', 'Я' => 'ja', 'Э' => 'je', 'э' => 'je', 'ё' => 'jo', 'Ё' => 'jo', 'ю' => 'ju', 'Ю' => 'ju',
            'ĸ' => 'k', 'כ' => 'k', 'Ķ' => 'k', 'К' => 'k', 'к' => 'k', 'ķ' => 'k', 'ך' => 'k',
            'Ŀ' => 'l', 'ŀ' => 'l', 'Л' => 'l', 'ł' => 'l', 'ļ' => 'l', 'ĺ' => 'l', 'Ĺ' => 'l', 'Ļ' => 'l', 'л' => 'l', 'Ľ' => 'l', 'ľ' => 'l', 'ל' => 'l',
            'מ' => 'm', 'М' => 'm', 'ם' => 'm', 'м' => 'm',
            'ñ' => 'n', 'н' => 'n', 'Ņ' => 'n', 'ן' => 'n', 'ŋ' => 'n', 'נ' => 'n', 'Н' => 'n', 'ń' => 'n', 'Ŋ' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'Ň' => 'n', 'ň' => 'n',
            'о' => 'o', 'О' => 'o', 'ő' => 'o', 'õ' => 'o', 'ô' => 'o', 'Ő' => 'o', 'ŏ' => 'o', 'Ŏ' => 'o', 'Ō' => 'o', 'ō' => 'o', 'ø' => 'o', 'ǿ' => 'o', 'ǒ' => 'o', 'ò' => 'o', 'Ǿ' => 'o', 'Ǒ' => 'o', 'ơ' => 'o', 'ó' => 'o', 'Ơ' => 'o', 'œ' => 'oe', 'Œ' => 'oe', 'ö' => 'oe',
            'פ' => 'p', 'ף' => 'p', 'п' => 'p', 'П' => 'p',
            'ק' => 'q',
            'ŕ' => 'r', 'ř' => 'r', 'Ř' => 'r', 'ŗ' => 'r', 'Ŗ' => 'r', 'ר' => 'r', 'Ŕ' => 'r', 'Р' => 'r', 'р' => 'r',
            'ș' => 's', 'с' => 's', 'Ŝ' => 's', 'š' => 's', 'ś' => 's', 'ס' => 's', 'ş' => 's', 'С' => 's', 'ŝ' => 's', 'Щ' => 'sch', 'щ' => 'sch', 'ш' => 'sh', 'Ш' => 'sh', 'ß' => 'ss',
            'т' => 't', 'ט' => 't', 'ŧ' => 't', 'ת' => 't', 'ť' => 't', 'ţ' => 't', 'Ţ' => 't', 'Т' => 't', 'ț' => 't', 'Ŧ' => 't', 'Ť' => 't', '™' => 'tm',
            'ū' => 'u', 'у' => 'u', 'Ũ' => 'u', 'ũ' => 'u', 'Ư' => 'u', 'ư' => 'u', 'Ū' => 'u', 'Ǔ' => 'u', 'ų' => 'u', 'Ų' => 'u', 'ŭ' => 'u', 'Ŭ' => 'u', 'Ů' => 'u', 'ů' => 'u', 'ű' => 'u', 'Ű' => 'u', 'Ǖ' => 'u', 'ǔ' => 'u', 'Ǜ' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'У' => 'u', 'ǚ' => 'u', 'ǜ' => 'u', 'Ǚ' => 'u', 'Ǘ' => 'u', 'ǖ' => 'u', 'ǘ' => 'u', 'ü' => 'ue',
            'в' => 'v', 'ו' => 'v', 'В' => 'v',
            'ש' => 'w', 'ŵ' => 'w', 'Ŵ' => 'w',
            'ы' => 'y', 'ŷ' => 'y', 'ý' => 'y', 'ÿ' => 'y', 'Ÿ' => 'y', 'Ŷ' => 'y',
            'Ы' => 'y', 'ž' => 'z', 'З' => 'z', 'з' => 'z', 'ź' => 'z', 'ז' => 'z', 'ż' => 'z', 'ſ' => 'z', 'Ж' => 'zh', 'ж' => 'zh'
        );
        return strtr($s, $replace);
    }

    public function actionForNormalityTestRatings()
    {
        $path = Yii::getAlias('@console') . '/files';
        $fileName = '/users-real-nt.txt';
        $file = fopen($path . $fileName, 'wb');
        $movieGenre = Genre::find()->all();
        foreach($movieGenre as $genre) {
            $this->saveRatings($file, $genre->id);
        }
        fclose($file);

    }

    public function saveRatings($file, $genre_id)
    {

        $ratings = Rating::find()->where(['genre_id' => $genre_id])->all();
        foreach($ratings as $rating)
        {
            fwrite($file, $rating->rating . PHP_EOL);
        }
        var_dump('Zapisano dla gatunku ' . $genre_id);
    }

    public function actionForNormalityTestMovies()
    {
        $path = Yii::getAlias('@console') . '/files';
        $fileName = '/movies-real-nt.txt';
        $file = fopen($path . $fileName, 'wb');
        $movies = Movie::find()->all();
        foreach($movies as $movie)
        {
            fwrite($file, $movie->rating . PHP_EOL);
        }
        fclose($file);
    }

    public function actionPrepareWrongData()
    {
       $allData = Help::find()->where(['checked' => null])->orderBy(new Expression('rand()'))->limit(500)->all();
       $svm = Helper::trainSvm();
       var_dump('trained');
       $count = 0;
       foreach ($allData as $data) {
           var_dump($data->movie_id . ' - ' . $data->rating);
           $predicted = Helper::repareOne($data, $svm);
           if($predicted) {
               $add = rand(0,1) == 1;
               if(5-$predicted <= 2) {
                   $value = 2;
               } else {
                   $value = rand(2, 5-$predicted);
               }
               if($add) {
                   $new = $predicted + $value;
                   if($new > 5) {
                       $new = $predicted - $value;
                   }
               } else {
                   $new = $predicted - $value;
                   if($new < 0.5) {
                       $new = $predicted - $value;
                   }
               }
               var_dump($data->user_id . ' - ' . $data->movie_id . ' stara: ' . $data->rating . ' nowa: ' . $new);
               $data->rating = $new;
               $data->correct = 0;
               $data->update(false, ['rating', 'correct']);
               $count++;
           }
       }
       var_dump('prepared wrong: ' . $count);
    }

}