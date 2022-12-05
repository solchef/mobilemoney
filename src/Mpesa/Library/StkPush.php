<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Library;
use Jawiwy\MobileMoney\src\Mpesa\Library\CurlEngine;
use GuzzleHttp\Exception\RequestException;
use Ixudra\Curl\Facades\Curl;
use Carbon\Carbon;
use Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities\MpesaStkRequest;
use Illuminate\Support\Facades\Auth;

class StkPush extends  CommonClass
{
    /**
     * @var string
     */
    protected $number;
    /**
     * @var int
     */
    protected $amount;
    /**
     * @var string
     */
    protected $reference;
    /**
     * @var string
     */
    protected $descrption;
    /**
     * @var \Jawiwy\MobileMoney\src\Mpesa\Library\CurlEngine
     */
    public $tokengenerator;

    /**
     * StkPush constructor.
     */
    public function __construct()
    {
        $this->tokengenerator = new CurlEngine();
    }

    /**
     * @param $amount
     * @return StkPush
     * @throws \Exception
     */
    public function checknumeric($amount){
        //check if numeric
        if(!is_numeric($amount)){
            throw new \Exception('The amount must be numeric, got ' . $amount);
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param $reference
     * @param $description
     * @return $this|array
     */
    public function checkalphanumeric($reference,$description){
        //
        preg_match('/[^A-Za-z0-9]/', $reference, $matches);
        if (count($matches)) {
            $response = [
                'status' => 0,
                'message' => 'Reference should be alphanumeric.',
            ];
            return $response;
        }
        $this->reference = $reference;
        $this->description = $description;

        return $this;
    }

    /**
     * @param $number
     * @return $this
     */
    public function formatphone($number){
        //
        $this->number = $this->formatPhoneNumber($number);
        return $this;
    }

    /**
     * @param null $msisdn
     * @param null $amount
     * @param null $reference
     * @param null $desc
     * @return mixed
     */
    public function makecurl($msisdn = null,$amount = null,$reference=null,$desc = null){
        //perform a curl request
		
        $PASSKEY = \config('mobilemoney.c2b.passkey');
        $timestamp = \Carbon\Carbon::now()->format('YmdHis');
        $shortcode = \config('mobilemoney.c2b.short_code');
        $curl_post_data = array(
            //Fill in the request parameters with valid values
            'BusinessShortCode' => $shortcode,
            'Password' => base64_encode($shortcode.$PASSKEY.$timestamp),
            'Timestamp' =>$timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $this->amount,
            'PartyA' => $this->number,
            'PartyB' => $shortcode,
            'PhoneNumber' => $this->number,
            'CallBackURL' => \config('mobilemoney.c2b.stk_callback'),
            'AccountReference' => $this->number,
            'TransactionDesc' => 'M-Transfer payments'
        );
        $endpoint = $this->tokengenerator->build("stk_push");
        $response = $this->makeRequest($curl_post_data,$endpoint);
        return $this->saveStkRequest($curl_post_data, (array)$response->content);
        // return response()->json($response);
    }
    public function validatestk($checkoutrequestID){


        if ((int)$checkoutrequestID) {
            $checkoutrequestID = MpesaStkRequest::where("CheckoutRequestID",$checkoutrequestID)->first();

        }
        $time = Carbon::now()->format('YmdHis');
        $passkey = \config('mobilemoney.c2b.passkey');
        $shortCode = \config('mobilemoney.c2b.short_code');
        $password = \base64_encode($shortCode . $passkey . $time);
        $body = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $time,
            'CheckoutRequestID' => $checkoutrequestID,
        ];
        try {
            $response = $this->sendRequest($body, 'stk_status');
            $_body = $response->content;
            if($response->status !== 200){
                $status = $_body->errorMessage;
                throw new \Exception("Error occured ".$status);
            }
            return $_body;
        } catch (RequestException $exception) {
            throw new \Exception($exception->getMessage());
        }

    }


    public function savestkrequest($body,$response){
        //save the request posted
		//return $response['errorMessage'];
        if (isset($response['ResponseCode']) AND $response['ResponseCode'] == 0) {
			 $incoming = [
                'phone' => $body['PartyA'],
                'amount' => $body['Amount'],
                'reference' => $body['AccountReference'],
                'description' => $body['TransactionDesc'],
                'CheckoutRequestID' => $response['CheckoutRequestID'],
                'MerchantRequestID' => $response['MerchantRequestID'],
                'user_id' => @(Auth::id() ?: request('user_id')),
            ];
            $stk = MpesaStkRequest::create($incoming);
            // event(new StkPushRequestedEvent($stk, request()));
            return $stk;
        }
        throw new \Exception($response['errorMessage']);
    }

}