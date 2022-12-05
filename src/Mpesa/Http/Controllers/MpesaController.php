<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Http\Controllers;
use App\Http\Controllers\Controller;
use Jawiwy\MobileMoney\src\Mpesa\Library\Mpesa;
use Jawiwy\MobileMoney\src\Mpesa\Library\BulkSender;
use Illuminate\Http\Request;
use Request as Newrequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DB;
class MpesaController extends Controller
{
    /**
     * @var Mpesa
     */
    private $repository;
    private $bulksender;

    /**
     * MpesaController constructor.
     * @param Mpesa $repository
     * @param BulkSender $bulkSender
     */
    public function __construct(Mpesa $repository,BulkSender $bulkSender)
    {
        $this->repository = $repository;
        $this->bulksender = $bulkSender;
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){
        return view('Jawiwy::welcome');
    }

    /**
     * @param Request $request
     * @return string
     */
    public function confirmation(Request $request){
        $resp = $this->repository->processConfirmation(json_encode($request->all()));
        return response()->json($resp);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function  validatepayment(Request $request){
        //log the same in the logs
        log::debug(json_encode($request->all()));

        $resp = $this->repository->processValidation(json_encode($request->all()));

        return response()->json($resp);
    }

    /**
     * @return string
     */
    public function stkCallback(Request $request)
    {
        //var_dump($request->all()['Body']);
        // exit;
        $this->repository->StkPushCallback(json_encode($request->all()['Body']));
        // return $this->repository->StkPushCallback($request);
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'STK Callback received successfully',
        ];
        return response()->json($resp);
    }
    public function initiateb2c(Request $request){
        //receive the B2C Request
       // $data = $request->all();
       /*  $validator = Validator::make($data,[
            'amount' => 'required|numeric',
            'phone' => 'required|numeric',
            'remarks' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        } */
        //get the params
		$data = DB::table('mt_outpayments')->where(array('status'=>0,'channel'=>1))->limit(1)->get();
		foreach($data as $datarray){
        try {
            $bulk [] = array($this->bulksender->to($datarray->msisdn)
                ->amount($datarray->amount)
                ->withRemarks("M-Transfer Payments")
				->scheduleid($datarray->schedule)
                ->send());
			DB::table('mt_outpayments')->where(array('id'=>$datarray->id))->update(array("status"=>1));
			log::debug("DB Request response ".json_encode($bulk));
	   } catch (\Exception $exception) { 
			DB::table('mt_outpayments')->where(array('id'=>$datarray->id))->update(array("status"=>2));
            $bulk [] = array(['ResponseCode' => 900, 'ResponseDescription' => 'Invalid request', 'extra' => $exception->getMessage()]);
			log::debug("DB Request response ".json_encode($bulk));
        }
		}
		
        return @$bulk?:"No Data";

    }
    //handles MPESA Timeout for B2C
    public function queuetimeout(){
        log::debug("Timeout ".json_encode(NewRequest::all()));
        return "Received";
    }
	public function result(Request $request,$initiator = null)
    {
       // $this->repository->notification('Incoming result: *' . $initiator . '*');
		log::debug(json_encode(NewRequest::all()));
		log::debug(json_encode($initiator));
        $this->repository->handleResult(NewRequest::all(),$initiator);
        return response()->json(
            [
                'ResponseCode' => '00000000',
                'ResponseDesc' => 'success'
            ]
        );
    }
    public function callback(Request $request){
        //the callback data to logged
        log::debug(json_encode($request));
        $resp = [
            'ResultCode' => 0,
            'ResultDesc' => 'Callback received successfully',
        ];
        return response()->json($resp);
    }
	public function balance_b2c(){
		return json_encode($this->bulksender->balance_b2c(),true);
	}
	public function balance_c2b(){
		return json_encode($this->bulksender->balance_c2b(),true);
	}
	public function b2btransfer(){
		return json_encode($this->bulksender->b2btransfer(),true);
	}
	public function b2b_status(Request $request,$id){
	    //handles B2C request status

        $response = $this->bulksender->b2c_status($id);
       // log::debug($response);
        return $response;
    }
    public function b2breversal(){
	    $phone = "354724619830";
	    $amout = 10;
	    $transid = "GwT24930Y";
        $response = $this->bulksender->reversal($phone,$amout,$transid);
        // log::debug($response);
        return $response;
    }
	
}