<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Library;
use Jawiwy\MobileMoney\src\Mpesa\Library\Mpesa;
use Jawiwy\MobileMoney\src\Mpesa\Library\CurlEngine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class BulkSender extends CommonClass
{
    private $msisdn;
    private $remarks;
    private $amount;
    private $trials;
    public  $bulk;
    public $mpesa;
    public $scheduleid;

    /**
     * BulkSender constructor.
     * @param \Jawiwy\MobileMoney\src\Mpesa\Library\Mpesa $mpesa
     */
    public function __construct(Mpesa $mpesa)
    {
        $this->mpesa = new Mpesa();
    }

    public function to($msisdn){
        //msisdn
        $this->msisdn = $this->formatPhoneNumber($msisdn);
        return $this;
    }
    public function withRemarks($remarks)
    {
        $this->remarks = $remarks;
        return $this;
    }
    public function amount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
	public function scheduleid($scheduleid){
		$this->scheduleid = $scheduleid;
        return $this;
	}
    public function send($number = null, $amount = null, $remarks = null)
    {

        $body = [
            'InitiatorName' => \config('mobilemoney.b2c.initiator'),
            'SecurityCredential' => \config('mobilemoney.b2c.security_credential'),
            'CommandID' => 'BusinessPayment', //SalaryPayment,BusinessPayment,PromotionPayment
            'Amount' => $amount ?: $this->amount,
            'PartyA' => \config('mobilemoney.b2c.short_code'),
            'PartyB' => $this->formatPhoneNumber($number ?: $this->msisdn),
            'Remarks' => $remarks ?: $this->remarks,
            'QueueTimeOutURL' => \config('mobilemoney.b2c.timeout_url') . 'b2c',
            'ResultURL' => \config('mobilemoney.b2c.result_url') . 'b2c',
            'Occasion' => $this->scheduleid
        ];

        $this->bulk = true;
        //var_dump($this->sendRequest($body, 'b2c'));
		
        try {
			$endpoint = CurlEngine::build('b2c');
			$response = $this->makeRequestB2C($body,$endpoint);
			log::debug("Request response for ".$this->scheduleid."-".json_encode($response));
			if($response->status !== 200){
				 throw new \Exception('Invalid request');
			}else{
				 return $this->mpesa->saveB2cRequest($response->content, $body);
			}
           
        } catch (ServerException $exception) {
            if ($this->trials > 0) {
              $this->trials--;
			  log::debug(json_encode($exception)." trials".$this->trials);
               // return $this->send($number, $amount, $remarks);
            }
            throw new \Exception('Server Error');
        }
    }
    public function balance_c2b()
    {
        $body = [
            'CommandID' => 'AccountBalance',
            'Initiator' => \config('mobilemoney.c2b.initiator'),
            'SecurityCredential' => \config('mobilemoney.c2b.security_credential'),
            'PartyA' => \config('mobilemoney.c2b.short_code'),
            'IdentifierType' => 4,
            'Remarks' => 'Checking Balance',
            'QueueTimeOutURL' => \config('mobilemoney.c2b.timeout_url') . 'c2b_balance',
            'ResultURL' => \config('mobilemoney.c2b.result_url') . 'c2b_balance',
        ];
        /** @var TYPE_NAME $this */
        $this->bulk = true;
		$endpoint = CurlEngine::build('account_balance');
        return $this->makeRequestC2B($body, $endpoint);
    }
	public function balance_b2c()
    {
        $body = [
            'CommandID' => 'AccountBalance',
            'Initiator' => \config('mobilemoney.b2c.initiator'),
            'SecurityCredential' => \config('mobilemoney.b2c.security_credential'),
            'PartyA' => \config('mobilemoney.b2c.short_code'),
            'IdentifierType' => 4,
            'Remarks' => 'Checking Balance',
            'QueueTimeOutURL' => \config('mobilemoney.b2c.timeout_url') . 'bulk_balance',
            'ResultURL' => \config('mobilemoney.b2c.result_url') . 'bulk_balance',
        ];
        /** @var TYPE_NAME $this */
        $this->bulk = true;
		$endpoint = CurlEngine::build('account_balance');
        return $this->makeRequestB2C($body, $endpoint);
		//return $this->mpesa->savebalancecheck($response->content);
    }
	public function b2btransfer(){
		$threshold = \config('mobilemoney.c2b.minthreshold');
		$this->balance_c2b();
		$balance = DB::table('mt_balances')->where(array('type'=>"c2b_balance"))->value('amount');
		if($balance >= $threshold){
			$amount = ($balance - \config('mobilemoney.c2b.minbalance'));
		$body = [
				"Initiator" => \config('mobilemoney.c2b.initiator'),
				"SecurityCredential" => \config('mobilemoney.c2b.security_credential'),
				"CommandID"=> "BusinessToBusinessTransfer", 
				"SenderIdentifierType"=>"4",
				"RecieverIdentifierType"=>"4", 
				"Amount"=>$amount,
				"PartyA" => \config('mobilemoney.c2b.short_code'),
				"PartyB"=>\config('mobilemoney.b2c.short_code'),
				"AccountReference"=>"4",
				"Remarks"=>"Test payments",
				"QueueTimeOutURL"=>\config('mobilemoney.b2c.timeout_url') . 'b2b',
				"ResultURL"=>\config('mobilemoney.b2c.result_url') . 'b2b',
			];
		//return json_encode($body;
		$endpoint = CurlEngine::build('b2b');
	
        return $this->makeRequestC2B($body, $endpoint);
		}
		return null;
	}
	public function b2c_status($id){
	   //
        $origin_id = DB::table('mt_outpayments')->where(array("schedule"=>$id))->value('origin_id');
          if($origin_id){
            $status_report = DB::table('mpesa_bulk_payments_status')->where(array('originatorID'=>$origin_id))->first();
            if(count($status_report) > 0){
                $parameters = json_decode($status_report->results)->ResultParameter;
               // log::debug($parameters->Key);
                $html = "";
                foreach($parameters as $parameter){
                   // log::debug(@$parameter->Value);
                    $html .= "<div class='form-group'><label class='control-label'>".@$parameter->Key."</label><input type='text' id='applevel' class='form-control' disabled value=".@$parameter->Value."  ></div>";
                }
                return array("status"=>1,"content"=>$html);




            }else{
                $body = [
                    'CommandID' => 'TransactionStatusQuery',
                    'Initiator' => \config('mobilemoney.b2c.initiator'),
                    'SecurityCredential' => \config('mobilemoney.b2c.security_credential'),
                    'OriginalConversationID' => $origin_id,
                    'PartyA' => \config('mobilemoney.b2c.short_code'),
                    'IdentifierType' => 4,
                    'Remarks' => 'Checking Transaction status',
                    'QueueTimeOutURL' => \config('mobilemoney.b2c.timeout_url') . 'trans_status',
                    'ResultURL' => \config('mobilemoney.b2c.result_url') . 'trans_status',
                ];
                /** @var TYPE_NAME $this */
                //$this->bulk = true;
                $endpoint = CurlEngine::build('transaction_status');
                $response = $this->makeRequestB2C($body, $endpoint);
                log::debug(json_encode($response));
                if($response->status === 200){
                    $content = $response->content->ResponseDescription;
                    return $content.". Plese check after some few Minutes";
                }else{
                    return "An error occured. Please try again later";
                }
            }

        }else{
            return "Unknown Transaction. Please try gain later";
        }

    }
    public function reversal($ReceiverParty,$Amount,$TransactionID){
        $body = array(
            'CommandID' => "TransactionReversal",
            'Initiator' => \config('mobilemoney.b2c.initiator'),
            'SecurityCredential' => \config('mobilemoney.b2c.security_credential'),
            'TransactionID' => $TransactionID,
            'Amount' => $Amount,
            'ReceiverParty' => $ReceiverParty,
            'RecieverIdentifierType' => 4,
            'ResultURL' => \config('mobilemoney.b2c.result_url') . 'reversal',
            'QueueTimeOutURL' => \config('mobilemoney.b2c.timeout_url') . 'reversal',
            'Remarks' => "payment reversal",
            'Occasion' => ""
        );
        $endpoint = CurlEngine::build('reversal');
        return json_encode($this->makeRequestB2C($body, $endpoint));
    }

}