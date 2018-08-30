<?php
header("Content-type: text/html; charset=utf-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);


$urls[] = 'http://wot-news.com';
$urls[] = 'http://wot-news.com';
$urls[] = 'http://wot-news.com';
$urls[] = 'http://wot-news.com';
$urls[] = 'http://wot-news.com';
$urls[] = 'http://wot-news.com';
$urls[] = 'https://deangrant.wordpress.com';
$urls[] = 'https://deangrant.wordpress.com';
$urls[] = 'https://deangrant.wordpress.com';
$urls[] = 'https://deangrant.wordpress.com';
$urls[] = 'https://jqueryui.com/tabs/';
$urls[] = 'https://jqueryui.com/tabs/';
$urls[] = 'https://api.worldoftanks.ru/wot/ratings/accounts/?application_id=54b29552a32dd5f3ade861259e38a368&account_id=297981&type=all';
$urls[] = 'https://api.worldoftanks.ru/wot/ratings/accounts/?application_id=54b29552a32dd5f3ade861259e38a368&account_id=297981&type=all';
$urls[] = 'https://jqueryui.com/tabs/';
$urls[] = 'https://jqueryui.com/tabs/';
$urls[] = 'https://api.worldoftanks.ru/wot/ratings/accounts/?application_id=54b29552a32dd5f3ade861259e38a368&account_id=297981&type=all';
$urls[] = 'https://api.worldoftanks.ru/wot/ratings/accounts/?application_id=54b29552a32dd5f3ade861259e38a368&account_id=297981&type=all';

require '../vendor/autoload.php';

//$get = edrard\Curl\CurlNoFollow::exec('http://wot-news.com/r_test');

$curl = new edrard\Curl\Curl();

$time_start = microtime(true);

foreach($urls as $key => $link){
    $curl->addSession( $link, $key );
}
$tmp = $curl->execSingle(0);
//echo $tmp;
//$tmp = $curl->exec();

print_r($tmp);

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo '<b>Total Execution Time:</b> '.$execution_time.' Seconds'."\n";  