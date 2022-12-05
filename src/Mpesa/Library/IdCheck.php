<?php


namespace Jawiwy\MobileMoney\src\Mpesa\Library;
use Carbon\Carbon;

class IdCheck extends CommonClass
{
    public function validate($number, $callback = null)
    {
        $number = $this->formatPhoneNumber($number);
        $time = Carbon::now()->format('YmdHis');
        $shortCode = \config('mpesa.c2b.short_code');
        $passkey = \config('mpesa.c2b.passkey');
        $defaultCallback = \config('mpesa.c2b.id_validation_callback');
        $initiator = \config('mpesa.c2b.initiator');
        $password = \base64_encode($shortCode . $passkey . $time);
        $body = [
            'Initiator' => $initiator,
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $time,
            'TransactionType' => 'CheckIdentity',
            'PhoneNumber' => $number,
            'CallBackURL' => $callback ?: $defaultCallback,
            'TransactionDesc' => ' '
        ];
        return $this->sendRequest($body, 'id_check');
    }
}