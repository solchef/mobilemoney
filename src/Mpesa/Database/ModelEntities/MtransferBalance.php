<?php

namespace Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MtransferBalance
 * @package Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities
 */
class MtransferBalance extends Model
{
    protected $guarded = [];

    protected $table = 'mt_balances';
}
