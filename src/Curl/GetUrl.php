<?php

namespace edrard\Curl;

/**
* Get Url with retry and pauses;
*/

class GetUrl
{
    public static function getSingle($url, $retry, Callable $function = NULL){
        $res = FALSE;
        while( $retry >= 0 && $res === FALSE) 
        {
            if(!$url){
                $res = '';
                break;
            } 
            $sleep = $function !== NULL ? $function($retry) : 1;
            sleep($sleep);
            $ctx = stream_context_create(array('http'=>
                array(
                    'timeout' => 15, // 1 200 Seconds = 20 Minutes
                )
            ));  
            $res = file_get_contents($url, false, $ctx);  
            $retry--;
        }
        return $res;
    }  
}