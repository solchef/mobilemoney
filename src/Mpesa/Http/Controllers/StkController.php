<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Http\Controllers;

use App\Http\Controllers\Controller;
use Jawiwy\MobileMoney\src\Mpesa\Http\Requests\StkRequest;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Request as NewRequest;
use Jawiwy\MobileMoney\src\Mpesa\Library\StkPush as PushRequest;
class StkController extends Controller
{
    public $alt = false;
    protected $stk;

    public function __construct()
    {
        $this->stk = new PushRequest();
    }

    public function initiatepush(Request $request){
        $data = NewRequest::all();
        $validator = Validator::make($data,[
            'amount' => 'required|numeric',
            'phone' => 'required',
            'reference' => 'required',
            'description' => 'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }
        //get the params
        try {

            $stk_output = $this->stk->checknumeric($data['amount'])
                ->formatphone($data['phone'])
                ->checkalphanumeric($data['reference'], $data['description'])
                ->makecurl();
        } catch (\Exception $exception) {
            $stk_output = ['ResponseCode' => 900, 'ResponseDescription' => 'Invalid request', 'extra' => $exception->getMessage()];
			//return $exception;
        }
        return $stk_output;
    }
    public function stkStatus($reference){
        try {
            return response()->json($this->stk->validatestk($reference));
        } catch (\Exception $exception) {
            $stk_output = ['ResponseCode' => 900, 'ResponseDescription' => 'Invalid request', 'extra' => $exception->getMessage()];
            return $stk_output;
        }

    }
}