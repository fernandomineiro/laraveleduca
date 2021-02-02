<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Notifications\Notifiable;

class Wirecard 
{
    use Notifiable, Cachable;
    
    static function createReferenceNotification($host, $url_callback){
        $action = $host . '/api/wirecard/preference-notification';

        return self::request('POST', $action, ['url_callback' => $url_callback]);
    }

    private static function request($method, $action, $data = array()){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $action,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response;
    }
}
