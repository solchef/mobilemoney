<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Library;
use Ixudra\Curl\Facades\Curl;
use Jawiwy\MobileMoney\src\Mpesa\Library\CurlEngine;
use Illuminate\Support\Facades\Log;
class CommonClass
{
    public function formatPhoneNumber($number, $strip_plus = true)
    {
        $number = preg_replace('/\s+/', '', $number);
        $replace = function ($needle, $replacement) use (&$number) {
            if (starts_with($number, $needle)) {
                $pos = strpos($number, $needle);
                $length = strlen($needle);
                $number = substr_replace($number, $replacement, $pos, $length);
            }
        };
        $replace('2547', '+2547');
        $replace('07', '+2547');
        if ($strip_plus) {
            $replace('+254', '254');
        }
        return $number;
    }
    public function makeRequestB2C($body, $endpoint)
    {
        if (!empty($key = CurlEngine::gettokenb2c())) {
            $token = $key;
      }else{
            $token = CurlEngine::generatetokenb2c();
       }
		Log::debug("b2c  ".json_encode($token));
        return Curl::to($endpoint)
            ->withHeader('Authorization:Bearer '.$token.'')
            ->withResponseHeaders()
            ->returnResponseObject()
            ->withData($body)
            ->asJson()
            ->post();
    }
	public function makeRequestC2B($body, $endpoint)
    {
        if (!empty($key = CurlEngine::gettokenc2b())) {
            $token = $key;
        }else{
            $token = CurlEngine::generatetokenc2b();
        }
		//Log::debug("c2b".$token);
        return Curl::to($endpoint)
            ->withHeader('Authorization:Bearer '.$token.'')
            ->withResponseHeaders()
            ->returnResponseObject()
            ->withData($body)
            ->asJson()
            ->post();
    }
    public function sendRequest($data,$section){
		
        $endpoint = CurlEngine::build($section);
        $response = $this->makeRequest($data,$endpoint);
        return $response;

    }
}