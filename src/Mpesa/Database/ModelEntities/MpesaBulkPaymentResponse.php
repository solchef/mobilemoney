<?php

namespace Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MpesaBulkPaymentResponse
 * @package Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities
 */
class MpesaBulkPaymentResponse extends Model
{
    protected $guarded = [];
	 //protected $table = 'mt_balances';
    public function request()
    {
        return $this->belongsTo(MpesaBulkPaymentRequest::class, 'ConversationID', 'conversation_id');
    }
}
