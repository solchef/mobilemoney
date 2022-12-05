<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities\MpesaStkRequest;
use Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities\MpesaBulkPaymentRequest;
use Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities\MpesaBulkPaymentResponse;
use Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities\MpesaC2bCallback;
use Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities\MobilePayments;
use Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities\MpesaStkCallback;
use Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities\MtransferBalance;
use App\Http\Controllers\PaymentsController;

class Mpesa
{
    public function StkPushCallback($json)
    {
        $object = json_decode($json);
        $data = $object->stkCallback;
        $real_data = [
            'MerchantRequestID' => $data->MerchantRequestID,
            'CheckoutRequestID' => $data->CheckoutRequestID,
            'ResultCode' => $data->ResultCode,
            'ResultDesc' => $data->ResultDesc,
        ];
        if ($data->ResultCode == 0) {
            $_payload = $data->CallbackMetadata->Item;
            foreach ($_payload as $callback) {
                $real_data[$callback->Name] = @$callback->Value;
            }
            $callback = MpesaStkCallback::create($real_data);
        } else {
            $callback = MpesaStkCallback::create($real_data);
        }
        return $callback;
    }
    public function saveB2cRequest($response, $body = [])
    {
		DB::table('mt_outpayments')->where(array('schedule'=>$body['Occasion']))->update(array("conversation_id"=>str_replace("-","",$response->ConversationID),"originator_conversation_id"=> str_replace("-","",$response->OriginatorConversationID),"origin_id"=>$response->OriginatorConversationID));
        return MpesaBulkPaymentRequest::create([
            'conversation_id' => $response->ConversationID,
            'originator_conversation_id' => $response->OriginatorConversationID,
            'amount' => $body['Amount'],
            'phone' => $body['PartyB'],
            'remarks' => $body['Remarks'],
            'CommandID' => $body['CommandID'],
            'user_id' =>@(Auth::id() ?: 0),
        ]);
    }
    public function processConfirmation($json)
    {
        $data = json_decode($json, true);
        $callback = MpesaC2bCallback::create($data);
		$this->saverequest($data);
        return $callback;
    }

    /**
     * @param $data
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function processValidation($data){
        $data_array = json_decode($data, true);
        $validator = Validator::make($data_array, [
            "TransAmount"=> 'required|numeric',
            "BusinessShortCode" => 'required|numeric',
            "BillRefNumber" => 'required|numeric'
        ]);
        if($validator->fails()){
			 $resp = [
                    'ResultCode' => 0,
                    'ResultDesc' => 'Validation passed successfully',
                ];
           // return response()->json($validator->messages(), 400);
        }else{
            $minimum_amount = \config('mpesa.c2b.mimimum_amount');
			/* if (User::where('email', '=', Input::get('email'))->exists()) {
				// user found
				} */
			$user = DB::table('tbl_organizations')->where(array('account_no'=>$data_array['BillRefNumber']))->count();
			log::debug($user);
            if($user > 0){
                $resp = [
                    'ResultCode' => 0,
                    'ResultDesc' => 'Validation passed successfully',
                ];
            }else{
                $resp = [
                    'ResultCode' => 1,
                    'ResultDesc' => 'Validation Failed',
                ];
            }
            //
            return $resp;
        }
    }

    private function handleB2cResult($request)
    {
       // $data = json_decode($request('Result'), true);
        $data = $request['Result'];
        $common = [
            'ResultType', 'ResultCode', 'ResultDesc', 'OriginatorConversationID', 'ConversationID', 'TransactionID'
        ];
        $seek = ['OriginatorConversationID' => $data['OriginatorConversationID']];
        /** @var MpesaBulkPaymentResponse $response */
        $response = null;
		$transactions = DB::table('mt_outpayments')->where(array("originator_conversation_id"=> str_replace("-","",$data['OriginatorConversationID'])))->get();
        if ($data['ResultCode'] !== 0) {
            if(count($transactions) > 0){
				foreach($transactions as $transaction){}
					DB::table('tbl_schedules')->where(array('id'=>$transaction->schedule))->update(array('schedule_status'=>4));
				$payments = new PaymentsController();
				$payments->failedschedule($transaction->schedule);
			}
			$response = MpesaBulkPaymentResponse::updateOrCreate($seek,
               array_only($data, $common));
            //  event(new B2cPaymentFailedEvent($response, $data));
           return $response;
		   
        }else{
        $resultParameter = $data['ResultParameters'];
        $data['ResultParameters'] = json_encode($resultParameter);
		if(count($transactions) > 0){
				foreach($transactions as $transaction){}
					DB::table('tbl_schedules')->where(array('id'=>$transaction->schedule))->update(array('schedule_status'=>3));
				
			}
        $response = MpesaBulkPaymentResponse::updateOrCreate($seek, array_except($data, ['ReferenceData']));
        return $response;
		}
    }
    public function handleResult($request,$initiator = null)
    {
        if ($initiator === 'b2c') {
            return $this->handleB2cResult($request);
        }elseif($initiator === "reversal"){

			log::debug(json_encode($request));
			return $this->handelreversal($request);
		}elseif($initiator === "trans_status"){
            log::debug(json_encode($request));
            return $this->savestatusreport($request);
        }elseif($initiator !== null){
			return $this->savebalancecheck($request,$initiator);
		}
        return;
    }

    public function queryStkStatus()
    {
        /** @var MpesaStkRequest[] $stk */
        $stk = MpesaStkRequest::whereDoesntHave('response')->get();
        $success = $errors = [];
        foreach ($stk as $item) {
            try {
                $status = mpesa_stk_status($item->id);
                if (isset($status->errorMessage)) {
                    $errors[$item->CheckoutRequestID] = $status->errorMessage;
                    continue;
                }
                $attributes = [
                    'MerchantRequestID' => $status->MerchantRequestID,
                    'CheckoutRequestID' => $status->CheckoutRequestID,
                    'ResultCode' => $status->ResultCode,
                    'ResultDesc' => $status->ResultDesc,
                    'Amount' => $item->amount,
                ];
                $errors[$item->CheckoutRequestID] = $status->ResultDesc;
                $callback = MpesaStkCallback::create($attributes);
                // $this->fireStkEvent($callback, get_object_vars($status));
            } catch (\Exception $e) {
                $errors[$item->CheckoutRequestID] = $e->getMessage();
            }
        }
        return ['successful' => $success, 'errors' => $errors];
    }

    public function saverequest($data){
        //save the request as new
		$payments = new MobilePayments();
		$payments->account = $data['BillRefNumber'];
		$payments->channel = 1;
		$payments->trxn_id = $data['TransID'];
		$payments->msisdn = $data['MSISDN'];
		$payments->amount = $data['TransAmount'];
        $payments->save();
    }
	public function savebalancecheck($data,$initiator){
		//$balancecheck = new MtransferBalance();
		$balance = @$data['Result']['ResultParameters']['ResultParameter'][0]['Value'];
		
		$working_account = @explode("|",explode("&",$balance)[1])[2];
		log::debug("current account".$working_account);
		//$balancecheck->updateOrCreate(["type"=>$initiator],["amount"=>$working_account]);
		$counter = DB::table('mt_balances')->where(array('type'=>$initiator))->count();
		if($counter > 0){
			DB::table('mt_balances')->where(array('type'=>$initiator))->update(array("amount"=>$working_account));
		}else{
			DB::table('mt_balances')->insertGetId(array("type"=>$initiator,"amount"=>$working_account));
		}
		return "Success";
	}
	public function savestatusreport($data){
	   // log::debug($data['Result']['OriginatorConversationID']);
        if($originatorID = DB::table('mpesa_bulk_payments_status')->where(array('originatorID' => $data['Result']['OriginatorConversationID']))->value('originatorID')) {
        }else{
            DB::table('mpesa_bulk_payments_status')->insert(array('conversationID' => $data['Result']['ConversationID'], 'originatorID' => $data['Result']['OriginatorConversationID'], 'status' => 1, 'results' => json_encode($data['Result']['ResultParameters'])));
        }
    }
    public function handelreversal($request){
       // $callbackJSONData=file_get_contents('php://input');
        $callbackJSONData=$request;
        $callbackData=json_decode($callbackJSONData);
        $resultType=$callbackData->Result->ResultType;
        $resultCode=$callbackData->Result->ResultCode;
        $resultDesc=$callbackData->Result->ResultDesc;
        $originatorConversationID=$callbackData->Result->OriginatorConversationID;
        $conversationID=$callbackData->Result->ConversationID;
        $transactionID=$callbackData->Result->TransactionID;
        $result=[
            "resultType"=>$resultType,
            "resultCode"=>$resultCode,
            "resultDesc"=>$resultDesc,
            "conversationID"=>$conversationID,
            "transactionID"=>$transactionID,
            "originatorConversationID"=>$originatorConversationID
        ];
        return json_encode($result);
    }
}