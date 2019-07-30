<?php

namespace edrard\Curl;

/**
* curlopt_followlocation cannot be activated when an open_basedir is set
* This is alternative function to keep go on redirects.
*/

class CurlNoFollow
{
    public static function exec($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        return static::curlRedirectExec($ch);
    }
    protected static function curlRedirectExec($ch, &$redirects = 0, $curlopt_header = false)
    {
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        //print_r($data); die;
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 301 || $http_code == 302) {
            list($header) = explode("\r\n\r\n", $data, 2);

            $matches = array();
            preg_match("/(Location:|URI:|location:)[^(\n)]*/", $header, $matches);
            $url = trim(str_replace($matches[1], "", $matches[0]));

            $url_parsed = parse_url($url);

            if (isset($url_parsed)) {
                curl_setopt($ch, CURLOPT_URL, $url);
                $redirects++;
                return static::curlRedirectExec($ch, $redirects, $curlopt_header);
            }
        }
        if ($curlopt_header) {
            return $data;
        } else {
            list(, $body) = explode("\r\n\r\n", $data, 2);
            return $body;
        }
        return false;
    }
}
