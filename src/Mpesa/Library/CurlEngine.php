<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Library;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Cache;

class CurlEngine
{
    public static  function generatetokenc2b(){
        //
        $CONSUMER_KEY = \config('mobilemoney.c2b.consumer_key');//"pTPNT1m5M7UJltMCGtJtXg9u187dDfNt";
        $CONSUMER_SECRET = \config('mobilemoney.c2b.consumer_secret');//"afEsVkXPSGUhO9Ic";
       $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
       //$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode($CONSUMER_KEY.":".$CONSUMER_SECRET);
        $response = Curl::to($url)
            ->withHeader('Authorization: Basic '.$credentials)
            ->withResponseHeaders()
            ->returnResponseObject()
            //->asJson() 
            ->get();
        if($response->status == 200){
            $token = json_decode($response->content)->access_token;
            self::savetokenc2b($token);
            return $token;
        }else{
            $resp = ["status"=>$response->status,"Message"=>"An error occured"];
            return $resp;
        }

    }
    public static  function savetokenc2b($token){
        Cache::put("tokenc2b",$token,30);
    }
    public static function gettokenc2b(){
        return Cache::get("tokenc2b");
    }
	public static  function generatetokenb2c(){
        //
        $CONSUMER_KEY = \config('mobilemoney.b2c.consumer_key');//"pTPNT1m5M7UJltMCGtJtXg9u187dDfNt";
        $CONSUMER_SECRET = \config('mobilemoney.b2c.consumer_secret');//"afEsVkXPSGUhO9Ic";
        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        //$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode($CONSUMER_KEY.":".$CONSUMER_SECRET);
        $response = Curl::to($url)
            ->withHeader('Authorization: Basic '.$credentials)
            ->withResponseHeaders()
            ->returnResponseObject()
            //->asJson()
            ->get();
        if($response->status == 200){
            $token = json_decode($response->content)->access_token;
            self::savetokenb2c($token);
            return $token;
        }else{
            $resp = ["status"=>$response->status,"Message"=>"An error occured"];
            return $resp;
        }

    }
    public static  function savetokenb2c($token){
        Cache::put("tokenb2c",$token,30);
    }
    public static function gettokenb2c(){
        return Cache::get("tokenb2c");
    }
    private static function getEndpoint($section)
    {
        $list = [
            'auth' => 'oauth/v1/generate?grant_type=client_credentials',
            'id_check' => 'mpesa/checkidentity/v1/query',
            'register' => 'mpesa/c2b/v1/registerurl',
            'stk_push' => 'mpesa/stkpush/v1/processrequest',
            'stk_status' => 'mpesa/stkpushquery/v1/query',
            'b2c' => 'mpesa/b2c/v1/paymentrequest',
            'reversal' => 'mpesa/reversal/v1/request',
            'transaction_status' => 'mpesa/transactionstatus/v1/query',
            'account_balance' => 'mpesa/accountbalance/v1/query',
            'b2b' => 'mpesa/b2b/v1/paymentrequest',
            'simulate' => 'mpesa/c2b/v1/simulate',
        ];
        if ($item = $list[$section]) {
            return self::getUrl($item); 
        }
        throw new \Exception('Unknown endpoint');
    }

    private static function getUrl($suffix)
    {
       $baseEndpoint = 'https://api.safaricom.co.ke/';
//        if (\config('samerior.mpesa.sandbox')) {
          //$baseEndpoint = 'https://sandbox.safaricom.co.ke/';
//        }
        return $baseEndpoint . $suffix;
    }

    public static function build($endpoint)
    {
        return self::getEndpoint($endpoint);
    }
}