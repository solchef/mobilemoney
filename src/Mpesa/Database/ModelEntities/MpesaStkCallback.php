<?php

namespace Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MpesaStkCallback
 * @package Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities
 */
class MpesaStkCallback extends Model
{
    protected $guarded = [];

    public function request()
    {
        return $this->belongsTo(MpesaStkRequest::class, 'CheckoutRequestID', 'CheckoutRequestID');
    }
}
