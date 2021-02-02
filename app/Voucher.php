<?php

namespace App;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Voucher extends Model
{
    use Notifiable, Cachable;
    
    protected $table = 'voucher';
    protected $fillable = ['fk_usuario', 'fk_pedido', 'fk_curso', 'url', 'code', 'criacao'];
    public $timestamps  = false;

    static function getVoucher($host, $pid, $type, $pedidos_item_id){
        $action = $host . '/api/voucher/' . $pid . '/' . $type . '/' . $pedidos_item_id;

        return self::request('GET', $action);
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
