<?php
namespace Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MpesaBulkPaymentRequest
 * @package Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities
 */
class MpesaBulkPaymentRequest extends Model
{
    protected $guarded = [];
	//protected $table = 'mpesa_bulk_payment_request';

    public function response()
    {
        return $this->hasOne(MpesaBulkPaymentResponse::class, 'ConversationID', 'conversion_id');
    }
}
