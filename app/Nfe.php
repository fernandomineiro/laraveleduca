<?php

namespace App;

use App\NfeConfig;

use Illuminate\Database\Eloquent\Model;

class Nfe extends Model
{
    protected $fillable = ['nfse_id', 'enviado', 'recebido', 'fk_pedido', 'invoice_id_wirecard', 'fk_assinatura', 'status', 'error', 'data_criacao', 'data_atualizacao'];
    protected $primaryKey = 'id';
    protected $table = "nfe";
    public $timestamps = false;

    private static $authorization_key;
    private static $company_id;
    private static $url_api;

    private static function authentication(){
        $config = NfeConfig::first();

        if (isset($config->authorization_key)){
            self::$authorization_key = $config->authorization_key;
            self::$company_id = $config->company_id;
            self::$url_api = $config->url_api;
        }
    }

    static function issueInvoice($data){
        self::authentication();
        
        $action = 'companies/' . self::$company_id . '/serviceinvoices';

        return self::request(self::$url_api, 'POST', $action, $data);
    }

    static function downloadInvoice($invoice_id){
        self::authentication();

        $action = 'companies/' . self::$company_id . '/serviceinvoices/' . $invoice_id . '/pdf';

        return self::requestDownload(self::$url_api . $action);
    }

    static function resentInvoice($invoice_id){
        self::authentication();

        $action = 'companies/' . self::$company_id . '/serviceinvoices/' . $invoice_id . '/sendemail';

        return self::request(self::$url_api, 'PUT', $action);
    }

    static function getIBGECode($cep){
        return self::request('viacep.com.br/ws/', 'GET', $cep . '/json/');
    }

    private static function request($url_api, $method, $action = '', $data = array()){
        self::authentication();

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url_api . $action,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array(
            "Authorization: " . self::$authorization_key,
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response;
    }

    private static function requestDownload($url) {
        set_time_limit(0);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Authorization: " . self::$authorization_key,
          "Content-Type: multipart/form-data",
        ));

        $r = curl_exec($ch);
        curl_close($ch);
    
        return $r;
    }
    
}
