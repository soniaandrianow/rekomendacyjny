<?php
/**
 * Created by PhpStorm.
 * User: Sofia
 * Date: 09.03.2018
 * Time: 14:46
 */

namespace console\controllers;


use common\models\Help;
use common\models\Movie;
use common\models\Rating;
use common\models\User;
use Yii;
use yii\console\Controller;

class FileController extends Controller
{
    public function actionReadInData()
    {
        $file_directory = "C:\Users\Sofia\Desktop\Praca_Magisterska\ml-1m";

        //$this->readInGenres($file_directory);

        var_dump('genres in db');

        //$this->readInUsers($file_directory);

        var_dump('users in db');

        //$this->readInMovies($file_directory);

        var_dump('movies in db');

        //$this->readInHelps($file_directory);

        var_dump('helps in db');

//        $movies = Movie::find()->all();
//        foreach ($movies as $movie) {
//            $this->countMovieRatings($movie);
//        }

        $users = User::find()->all();
        foreach($users as $user) {
            $this->createRatingsForUser($user);
        }
    }

    public function createRatingsForUser($user)
    {
        $helps = Help::find()->where(['user_id' => $user->id])->all();
        foreach ($helps as $help) {
            $movie = Movie::findOne(['id' => $help->movie_id]);
            $user = User::findOne(['id' => $help->user_id]);
            $rating_val = $help->rating;
            $genres = $movie->genres;
            foreach ($genres as $genre) {
                $this->countGenreRating($user, $genre, $rating_val);
            }
        }
    }

    public function readInUsers($file_directory)
    {
        $fil = fopen ($file_directory . "\users.dat", "r");
        $user_ex = new User();
        $users = [];
        while ($filedata = fgets($fil))
        {
            $q=explode('::', $filedata);
            $user = new User();
            $user->id = $q[0];
            $user->name = 'User' . $q[0];
            $users[] = $user;
        }
        Yii::$app->db->createCommand()->batchInsert(User::tableName(), $user_ex->attributes(), $users)->execute();
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

    public function readInMovies($file_directory)
    {
        $fil = fopen ($file_directory . "\movies.dat", "r");
        $movie_ex = new Movie();
        $movie_genre_ex = new MovieGenre();
        $movies = [];
        $movie_genres = [];
        while ($filedata = fgets($fil))
        {
            $q=explode('::', $filedata);
            $movie = new Movie();
            $movie->id = $q[0];
            $name = static::normalizeChars($q[1]);
            $movie->name = $name;
            $genres = explode('|', $q[2]);
            foreach ($genres as $genre) {
                $mg = new MovieGenre();
                $mg->movie_id = $movie->id;
                $mg->genre_id = Genre::findOne(['name' => trim($genre)])->id;
                $movie_genres[] = $mg;
                if(count($movie_genres) == 500) {
                    Yii::$app->db->createCommand()->batchInsert(MovieGenre::tableName(), $movie_genre_ex->attributes(), $movie_genres)->execute();
                    unset($movie_genres);
                    $movie_genres = [];
                }
            }
            $movies[] = $movie;
            if(count($movies) == 500) {
                Yii::$app->db->createCommand()->batchInsert(Movie::tableName(), $movie_ex->attributes(), $movies)->execute();
                unset($movies);
                $movies = [];
            }
        }
        Yii::$app->db->createCommand()->batchInsert(Movie::tableName(), $movie_ex->attributes(), $movies)->execute();
        Yii::$app->db->createCommand()->batchInsert(MovieGenre::tableName(), $movie_genre_ex->attributes(), $movie_genres)->execute();

    }

    public function readInHelps($file_directory)
    {
        $fil = fopen($file_directory . '\ratings.dat', "r");
        $help_ex = new Help();
        $helps = [];
        while ($filedata = fgets($fil)) {
            $q = explode('::', $filedata);
            $help = $this->createNewHelp($q);
            $helps[] = $help;
            if(count($helps) == 500) {
                Yii::$app->db->createCommand()->batchInsert(Help::tableName(), $help_ex->attributes(), $helps)->execute();
                unset($helps);
                $helps = [];
            }
        }
        Yii::$app->db->createCommand()->batchInsert(Help::tableName(), $help_ex->attributes(), $helps)->execute();
    }

    public function countMovieRatings($movie)
{
    $helps = Help::findAll(['movie_id' => $movie->id]);
    $sum = 0;
    $counter = 0;
    foreach ($helps as $help) {
        $sum += $help->rating;
        $counter +=1;
    }
    if($counter != 0) {
        $mr = $sum / $counter;
        $movie->rating = $mr;
        $movie->update(true, ['rating']);
    }
}
    public function countGenreRating($user, $genre, $rating_val)
    {
        $rating = Rating::find()->where(['user_id' => $user->id])->andWhere(['genre_id' => $genre->id])->one();
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

    public function createNewHelp($q)
    {
        $help = new Help();
        $help->movie_id = $q[1];
        $help->user_id = $q[0];
        $help->rating = $q[2];
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
}