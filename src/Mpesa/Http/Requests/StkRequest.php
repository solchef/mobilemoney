<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class StkRequest extends FormRequest
{
    public function rules()
    {
        return [
            'amount' => 'required|numeric',
            'phone' => 'required',
            'reference' => 'required',
            'description' => 'required',
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [];
    }

}