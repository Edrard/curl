<?php
namespace edrard\Curl;

/**
* Simple MultiCurl library with single URL retry
*/


class Curl
{
    protected $sessions                 =    array();
    protected $retry                    =    100; 
    protected $timeout                  =    60;
    protected $conn_timeout             =    10;
    protected $curl_retry               =    FALSE; 
    protected $sleep;
    protected $user_agent               =    FALSE;

    public function __construct(){
        $this->sleep = function($retry){
            return 1;
        };
    }
    public function setRetry($retry = 0){
        $this->retry = (int) $retry;
    }
    public function setTimeout($timeout = 0){
        $this->timeout = (int) $timeout;
    }
    public function setConTimelimit($conn_timeout = 0){
        $this->conn_timeout = (int) $conn_timeout;
    }
    public function setCurlRetry($curl_retry = 0){
        $this->curl_retry = (int) $curl_retry;
    }
    public function setSleep(Callable $function = NULL){
        $this->sleep = $function !== NULL ? $function : $this->sleep;
    }
    public function setUserAgent($user_agent = FALSE){
        $this->user_agent = (bool) $user_agent;
    }
    /**
    * Adds a Curl session to stack
    * @param $url string, session's URL
    * @param $opts array, optional array of Curl options and values
    */
    public function addSession( $url, $name, $opts = array(), $base_opts = TRUE )
    {
        $this->sessions[$name] = curl_init( $url );

        if($base_opts !== FALSE){
            if(!isset($opts['19913'])){
                $opts['19913'] = 1;
            }
            if(!isset($opts['52'])){
                $opts['52'] = 1;
            }
            if(!isset($opts['13'])){
                $opts['13'] = $this->timeout;
            } 
            if(!isset($opts['78'])){
                $opts['78'] = $this->conn_timeout;
            } 
        }
        if($this->user_agent !== FALSE && !isset($opts['10018'])){
            $opts['10018'] = Agents::get();
        }  
        $this->setOpts( $opts, $name );
    }

    /**
    * Sets an option to a Curl session
    * @param $option constant, Curl option
    * @param $value mixed, value of option
    * @param $key int, session key to set option for
    */
    public function setOpt( $option, $value, $key = 0 )
    {
        curl_setopt( $this->sessions[$key], $option, $value );
    }

    /**
    * Sets an array of options to a Curl session
    * @param $options array, array of Curl options and values
    * @param $key int, session key to set option for
    */
    public function setOpts( $options, $key = 0 )
    {
        curl_setopt_array( $this->sessions[$key], $options );
    }
    public function exec( $key = false )
    {
        $no = count( $this->sessions );
        $res = $this->execMulti();    
        if( $res )
            return $res;
    }
    public function execSingle( $id )
    {
        $res = '';
        if( $this->retry > 0 )
        {
            $retry = $this->retry;
            $code[0] = 0;
            while( $retry >= 0 && ($code[0] >= 400 || $code[0] == 0) ) 
            {
                $sleep = $this->sleep;
                sleep($sleep($this->retry));
                $res = curl_exec( $this->sessions[$id] );
                $code = $this->info( $id, CURLINFO_HTTP_CODE );          
                $retry--;
            }
        }else{
            foreach ( $this->sessions as $i => $url ){
                if($id == $i){
                    $res = curl_exec( $this->sessions[$i] );
                }
            }
        }
        return $res;
    }

    public function execMulti()
    {
        $mh = curl_multi_init();
        $res = array();   
        #Add all sessions to multi handle
        foreach ( $this->sessions as $i => $url )
            curl_multi_add_handle( $mh, $this->sessions[$i] );

        do
            $mrc = curl_multi_exec( $mh, $active );
        while ( $mrc == CURLM_CALL_MULTI_PERFORM );

        while ( $active && $mrc == CURLM_OK )
        {
            if (curl_multi_select($mh) == -1) { usleep(100); }

            do
                $mrc = curl_multi_exec( $mh, $active );
            while ( $mrc == CURLM_CALL_MULTI_PERFORM );
        }
        if ( $mrc != CURLM_OK )
            echo "Curl multi read error $mrc\n";

        #Get content foreach session, retry if applied
        foreach ( $this->sessions as $i => $url ){
            $code = $this->info( $i, CURLINFO_HTTP_CODE );
            $url_code = $this->info( $i );
            if( $code[0] > 0 && $code[0] < 400 ){
                $res[$i] = curl_multi_getcontent( $this->sessions[$i] );
            }else{
                if( $this->retry > 0 ){
                    $eRes = $this->curl_retry !== FALSE ? $this->execSingle( $i ) : GetUrl::getSingle( $url_code[0]['url'],$this->retry,$this->sleep);
                    $res[$i] = $eRes ? $eRes : FALSE;
                }
            }

            curl_multi_remove_handle( $mh, $this->sessions[$i] );
        }
        curl_multi_close( $mh );
        return $res;
    }

    /**
    * Closes Curl sessions
    * @param $key int, optional session to close
    */
    public function close( $key = false )
    {
        if( $key === false )
        {
            foreach( $this->sessions as $session )
                curl_close( $session );
        }
        else
            curl_close( $this->sessions[$key] );
    }

    /**
    * Remove all Curl sessions
    */
    public function clear()
    {
        foreach( $this->sessions as $session )
            curl_close( $session );
        unset( $this->sessions );
    }

    /**
    * Returns an array of session information
    * @param $key int, optional session key to return info on
    * @param $opt constant, optional option to return
    */
    public function info( $key = false, $opt = false )
    {
        if( $key === false )
        {
            foreach( $this->sessions as $key => $session )
            {
                if( $opt )
                    $info[] = curl_getinfo( $this->sessions[$key], $opt );
                else
                    $info[] = curl_getinfo( $this->sessions[$key] );
            }
        }
        else
        {
            if( $opt )
                $info[] = curl_getinfo( $this->sessions[$key], $opt );
            else
                $info[] = curl_getinfo( $this->sessions[$key] );
        }

        return $info;
    }

    /**
    * Returns an array of errors
    * @param $key int, optional session key to retun error on
    * @return array of error messages
    */
    public function error( $key = false )
    {
        if( $key === false )
        {
            foreach( $this->sessions as $session )
                $errors[] = curl_error( $session );
        }
        else
            $errors[] = curl_error( $this->sessions[$key] );

        return $errors;
    }
    /**
    * Returns an array of session error numbers
    * @param $key int, optional session key to retun error on
    * @return array of error codes
    */
    public function errorNo( $key = false )
    {
        if( $key === false )
        {
            foreach( $this->sessions as $session )
                $errors[] = curl_errno( $session );
        }
        else
            $errors[] = curl_errno( $this->sessions[$key] );

        return $errors;
    }
}
