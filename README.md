# Curl
Small Curl library with Multicurl


Simple Example
```
$urls[] = 'https://microsoft.com';
$urls[] = 'https://google.com';

$curl = new edrard\Curl\Curl();
$curl->setSleep(function($retry){
   return ceil($retry/50); 
});
//Adding Urls
foreach($urls as $key => $link){
    $curl->addSession( $link, $key );
}
// In $tmp we have result
$tmp = $curl->exec();
```