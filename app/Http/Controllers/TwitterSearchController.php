<?php

# Sra Sonsirikit created this class by his own.
# 2017-2018

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use XmlParser;
use GuzzleHttp;
use Illuminate\Support\Facades\File;
use DateTime;
use ErrorException;
use App\Helper\Laracurl\Laracurl;

class TwitterSearchController extends Controller
{

    public $month = 1;
    public $year = 2013;
    public $search_word = "ไข้เลือดออก";
    public $lang = "th";
    public $current_month = 1;
    public $current_year = 2010;
    public $run_date;
    public $LARACURL;


    function __construct()
    {
        $this->LARACURL = new Laracurl();
    }


    public function twitterSearchOneMonth(){

        set_time_limit(0);
        date_default_timezone_set('Asia/Bangkok');

        if (Input::has('q'))
        {
            $this->search_word = Input::get("q");
        }
        $setyear = 2010;
        if (Input::has('y'))
        {
            $setyear = Input::get("y");
        }
        $this->current_year = $setyear;
        $this->year = $setyear;


        $month_start = 1;
        if (Input::has('ms'))
        {
            $month_start = Input::get("ms");
        }

        $this->current_month = $month_start;
        $this->month = $month_start;


        $dt = new DateTime();
        $time = $dt->format('Y-m');
        $this->run_date = $time;

        $folder = public_path()."\\search_month\\".$this->search_word."\\".$time."\\";

        if (!file_exists($folder)) {
            File::makeDirectory($folder, 0775, true);
        }

        $file_name = $this->search_word."_".$time.".txt";
        $file_path = $folder.$file_name;
        $monthTweet = array();

        $urls = $this->getURLForSearch();
        $response = $this->LARACURL->get($urls);

        $result = $this->current_year.",".$this->current_month.",".$this->readTwitterSearchPage($response);
        $monthTweet[$this->current_month] = $result;

        File::append($file_path, $result);
        File::append($file_path, "\r\n");



        return $monthTweet;
    }

    public function twitterSearch(){

        set_time_limit(0);
        date_default_timezone_set('Asia/Bangkok');

        if (Input::has('q'))
        {
            $this->search_word = Input::get("q");
        }
        $setyear = 2010;
        if (Input::has('y'))
        {
            $setyear = Input::get("y");
        }
        $this->current_year = $setyear;

        $setyearend = 2010;
        if (Input::has('ye'))
        {
            $setyearend = Input::get("ye");
        }

        $month_start = 1;
        if (Input::has('ms'))
        {
            $month_start = Input::get("ms");
        }


        $dt = new DateTime();
        $time = $dt->format('Y-m-d_H-i-s');
        $this->run_date = $time;

        $folder = public_path()."\\search\\".$time."\\";

        if (!file_exists($folder)) {
            File::makeDirectory($folder, 0775);
        }

        $file_name = $this->search_word."_".$time.".txt";
        $file_path = $folder.$file_name;
        $monthTweet = array();

        for($this->year = $setyear; $this->year <= $setyearend ; $this->year++) {
            for($this->month = 1; $this->month <= 12 ; $this->month++){

                if($month_start != 1){
                    $this->month = $month_start;
                    $month_start = 1;
                }

                $urls = $this->getURLForSearch();


                $response = $this->LARACURL->get($urls);

                $result = $this->year.",".$this->month.",".$this->readTwitterSearchPage($response);
                $monthTweet[$this->month] = $result;

                File::append($file_path, $result);
                File::append($file_path, "\r\n");
            }
        }


        return $monthTweet;
    }




    public function getURLForSearch(){

        $searchWord = urlencode ( $this->search_word );
//        $url = "https://twitter.com/search?q=%E0%B9%84%E0%B8%82%E0%B9%89%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%94%E0%B8%AD%E0%B8%AD%E0%B8%81%20lang%3Ath%20since%3A2015-01-01%20until%3A2015-01-31&src=typd&lang=th";

        $since = $this->getSince();
        $until = $this->getUntil();


        $url = "https://twitter.com/search?q=".$searchWord."%20lang%3A".$this->lang."%20since%3A".$since."%20until%3A".$until."&src=typd&lang=".$this->lang;
        $urls = $this->LARACURL->buildUrl($url, ['s' => 'curl']);

        return $urls;
    }

    public function genURLForNextSearch($url_next){

//        $url = "https://twitter.com/i/search/timeline?vertical=default&q=%E0%B9%84%E0%B8%82%E0%B9%89%E0%B9%80%E0%B8%A5%E0%B8%B7%E0%B8%AD%E0%B8%94%E0%B8%AD%E0%B8%AD%E0%B8%81%20lang%3Ath%20since%3A2015-01-01%20until%3A2015-01-31&src=typd&include_available_features=1&include_entities=1&lang=th&max_position=TWEET-560811067832819712-561229138082533378-BD1UO2FFu9QAAAAAAAAETAAAAAcAAAASAAEBKAAAAAAAAACAAAABAAACAAAAAAAAAAAAQAAAAAAEAAAAAAAAAgAAAAAAAAAEAAAAAAAAAAQAAAAAAACIAAAAAAAIAABACBAAAAQAAAAAAAgAAAAAAAAAAAAAACAAAAACgAAAAAQAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAgAAAAAAACAAAAAAAAAAAEA&reset_error_state=false";

        $searchWord = urlencode ( $this->search_word );

        $since = $this->getSince();
        $until = $this->getUntil();

        $url = "https://twitter.com/i/search/timeline?vertical=default&q=".$searchWord."%20lang%3A".$this->lang."%20since%3A".$since."%20until%3A".$until."&src=typd&include_available_features=1&include_entities=1&lang=".$this->lang."&max_position=".$url_next."&reset_error_state=false";
        $urls = $this->LARACURL->buildUrl($url, ['s' => 'curl']);

        return $urls;
    }

    public function getJSONFromNextSearch($urls){

        $response = $this->LARACURL->get($urls);


        return $response;
    }

    public function genURLForNextSearch2($url_next){

        $searchWord = urlencode ( $this->search_word );

        $since = $this->getSince();
        $until = $this->getUntil();


        $url = "https://twitter.com/i/search/timeline?vertical=default&q=".$searchWord."%20lang%3A".$this->lang."%20since%3A".$since."%20until%3A".$until."&src=typd&include_available_features=1&include_entities=1&lang=".$this->lang."&max_position=".$url_next."&reset_error_state=false";
        $urls = $this->LARACURL->buildUrl($url, ['s' => 'curl']);

        return $urls;
    }

    public function readTwitterSearchPage($pageData){

        // First Response
        $body = $this->getBody($pageData);
        $tweets = $this->getTweetFromMainBody($body);
        $tweetCount = sizeof($tweets);
        $next = $this->getMainTweetURLFromMainPage($body);
        $next_url = $this->genURLForNextSearch($next);

        // Extended Response
        $json = $this->getJSONFromNextSearch($next_url);
        $response = json_decode($json);
        $tweetCount += $response->new_latent_count;
        $cur_html = $response->items_html;
        $time = $this->getFisrtTweetTimeFromInnerHtml($cur_html);
        $cur_month = (int) $this->monthOfYear($time);
        $this->current_month = $cur_month;
        $this->current_year = $this->TimeToYear($time);
        $this->getTweetFromMainBody($cur_html);

        $page_count = 1;
        // Deeper Extended Response
        while( $cur_month == $this->month && $time != 0){

            $previous_response = $response;
            $previous_count = $tweetCount;
            $previous_page = $page_count;
            $previous_month  = $cur_month;
            $previous_time = $time;
            try {

                $next_url = $this->genURLForNextSearch($response->min_position);

                $json = $this->getJSONFromNextSearch($next_url);
                $response = json_decode($json);
                $html = $response->items_html;
                $tweetCount += $response->new_latent_count;

                $cur_html = $response->items_html;
                $time = $this->getFisrtTweetTimeFromInnerHtml($cur_html);
                $cur_month = (int) $this->monthOfYear($time);
                $this->current_month = $cur_month;
                $this->current_year = $this->TimeToYear($time);
                $this->getTweetFromMainBody($html);

                $page_count++;


            } catch (ErrorException $e) {

                $response = $previous_response;
                $tweetCount = $previous_count;
                $time =  $previous_time ;
                $cur_month = $previous_month;
                $page_count = $previous_page;

            }

        }

        return $tweetCount.",".$page_count;
    }

    function getFisrtTweetTimeFromInnerHtml($html){

        $last_date = $this->get_string_between($html,"data-time", "data-time-ms");
        $array = explode("\"", $last_date);

        if(count($array) > 1){
            $last_date = $array[1];
        }else{
            return 0;
        }


        return $last_date;
    }

    function dayOfMonth($timestamp){
        return date("d", $timestamp);
    }

    function monthOfYear($timestamp){
        return date("m", $timestamp);
    }

    function TimeToYear($timestamp){
        return date("Y", $timestamp);
    }


    function getBody($pagedata){

        $body = $this->get_string_between($pagedata,"<body", "<body/>");
        $body = "<body".$body;


        return $body;
    }

    function getTweetFromMainBody($body){

        $tweets =  $this->retrieveTweetTextFromHtml($body);
        $this->saveTweetText($tweets);

        return $tweets;

    }


    function saveTweetText($tweets){

        if(sizeof($tweets) > 0){
            $firstTweet = $tweets[0];
            $dataFirstTweet = explode(",",$firstTweet);

            if(sizeof($dataFirstTweet) >= 4){
                $timeIndex = 4;
                $firstTweetTime = intval($dataFirstTweet[$timeIndex]) ;
                $firstTweetDate = date("Y_m", $firstTweetTime);

                $folder = public_path()."\\tweet\\".$this->search_word."\\".$this->run_date."\\";

                if (!file_exists($folder)) {
                    File::makeDirectory($folder, 0775, true);
                }

                $filename = $firstTweetDate.".csv";
                $file_path = $folder.$filename;


                if( file_exists ($file_path) == False) {
                    File::append($file_path, $this->getTweetDataHeader());
                    File::append($file_path, "\r\n");
                }

                foreach ($tweets as $tweet){
                    File::append($file_path, $tweet);
                    File::append($file_path, "\r\n");
                }
            }
        }
    }


    function retrieveTweetTextFromHtml($body){

        $chars = array();
        $steamItems = explode('stream-item-tweet-', $body);
        for ($i = 0 ; $i< count($steamItems) ;$i++){

            $text = $this->getFirstTweetText( $steamItems[$i]);

            if($text != null){
                $user_id = $this->getUserIdTweet($steamItems[$i]);
                $user_ScreenName = $this->getUserScreenNameTweet($steamItems[$i]);
                $user_DataName =  $this->cleanText( $this->getUserDataNameTweet($steamItems[$i]) );
                $time = $this->getTimeTweetText($steamItems[$i]);
                $time = $time / 1000;
                $steamItems[$i]  = trim($steamItems[$i]);
                $id = substr($steamItems[$i], 0,  strpos($steamItems[$i],'"') - strlen($steamItems[$i]) );
                $timeText = date("Y-m-d H:i:s", $time);
                $link = "/".$user_ScreenName."/status/".$id;
                $post = [$user_id,$user_ScreenName,$user_DataName,$id,$time,$timeText,$link,$text];
                $csvText = implode(",", $post);
                $chars[] = $csvText;
            }

        }


        return $chars;
    }


    function cleanText($text){

        $text = str_replace(',', ';', $text);

        return $text;
    }

    function getTweetDataHeader(){

        return "User_ID,User_ScreenName,User_DataName,Tweet_id,TimeStamp,DateTime,Link,Content";
    }


    function getUserIdTweet($body){

        $startWord = 'data-user-id="';
        $endWord = '"';

        return $this->getTextBetween($body,$startWord, $endWord);

    }

    function getUserScreenNameTweet($body){

        $startWord = 'data-screen-name="';
        $endWord = '"';

        return $this->getTextBetween($body,$startWord, $endWord);

    }

    function getUserDataNameTweet($body){

        $startWord = 'data-name="';
        $endWord = '"';

        return $this->getTextBetween($body,$startWord, $endWord);

    }

    function getLinkTweet($body){

        $startWord = 'data-permalink-path="';
        $endWord = '"';

        return $this->getTextBetween($body,$startWord, $endWord);

    }

    function getTextBetween($body,$startWord, $endWord ){

        $indexStart =  strpos($body,$startWord);
        $extractText = substr($body, $indexStart + strlen($startWord),  strlen($body) );
        $indexEnd =  strpos($extractText,$endWord);
        $extractText = substr($extractText, 0,  $indexEnd - strlen($extractText) );

        return $extractText;
    }


    function getTimeTweetText($body){

        $startWord = 'data-time-ms="';
        $endWord = '"';

        return $this->getTextBetween($body,$startWord, $endWord);

    }

    function getFirstTweetText($body){

        preg_match('<p class="TweetTextSize(.*)">', $body, $matches, PREG_OFFSET_CAPTURE);

        if(count($matches)>0){


            $chars =  explode('<p class="TweetTextSize', $body);
            for ($i = 0 ; $i< count($chars) ;$i++){
                $text = trim($chars[$i]);
                $text = substr($text, strpos($text,">"), strlen($chars[$i]) -1 );
                $chars[$i] = substr($text, 1,  strpos($text,"</p>") - strlen($text) );
            }
            for ($i = 0 ; $i< count($chars) ;$i++){
                if($chars[$i] == false){
                    unset($chars[$i]);
                }
            }
            $chars = array_values($chars);
            $text = $chars[0];

            $text = preg_replace('/(\r\n|\r|\n)+/', "\n", $text);
            // replace whitespace characters with a single space
            $text = preg_replace('/\s+/', ' ', $text);
            $text = str_replace(',', ';', $text);

            $highlightWord = "<strong>".$this->search_word."</strong>";
            $text = str_replace($highlightWord, $this->search_word, $text);

            return $text;
        }else{
            return null;
        }


    }

    function getMainTweetURLFromMainPage($body){

        $link = $this->get_string_between($body,'data-max-position="', "<body/>");
        $link = strtok($link, '"');

        return $link;
    }


    function getNextTweetURLFromMainPage($body){

        $link = $this->get_string_between($body,'data-min-position="', "<body/>");
        $link = strtok($link, '"');

        return $link;
    }

    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }


    function getSince(){

        return $this->year."-".$this->month."-01";

    }

    function getUntil(){

        $day = cal_days_in_month(CAL_GREGORIAN,$this->month, $this->year);

        return $this->year."-".$this->month."-".$day;

    }
}
