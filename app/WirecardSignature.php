<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WirecardSignature extends Model {
    private static $authorization;
    private static $enviroment;

    public static function authentication($access){
        self::$authorization = $access['auth'];
        self::$enviroment    = $access['enviroment'];
    }

    public static function getPlans(){
        return self::request('GET', '/plans');
    }

    public static function getPlan($plan_code){
        return self::request('GET', '/plans/' . $plan_code);
    }

    public static function getSubscriber($code){
        return self::request('GET', '/customers/' . $code);
    }

    public static function getSubscriptions($code){
        return self::request('GET', '/subscriptions/' . $code);
    }

    public static function getInvoices($code){
        return self::request('GET', '/subscriptions/' . $code . '/invoices/');
    }
        
    public static function getWebhook(){
        return self::request('GET', '/users/preferences');
    }

    public static function createPlan($data){
        return self::request('POST', '/plans', $data);
    }

    public static function createSignature($data){
        return self::request('POST', '/subscriptions?new_customer=false', $data);
    }
    
    public static function createSubscriber($data){
        return self::request('POST', '/customers?new_vault=true', $data);
    }
        
    public static function createWebhook($data){
        return self::request('POST', '/users/preferences', $data);
    }
        
    public static function updateSubscriber($code, $data){
        return self::request('PUT', '/customers/' . $code, $data);
    }

    public static function updatePlan($plan_wirecard_id, $data){
        return self::request('PUT', '/plans/' . $plan_wirecard_id, $data);
    }

    public static function cancelSignature($code){
        return self::request('PUT', '/subscriptions/' . $code . '/cancel');
    }

    public static function updateBillingInfos($code, $data){
        return self::request('PUT', '/customers/' . $code . '/billing_infos', $data);
    }

    private static function request($method, $action = '', $data = array()){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => self::$enviroment . $action,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POSTFIELDS =>  json_encode($data),
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array(
            self::$authorization,
            "Content-Type: application/json",
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        
        return $response;
    }
}

