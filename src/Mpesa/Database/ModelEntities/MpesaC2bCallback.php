<?php

namespace Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MpesaC2bCallback
 * @package Jawiwy\MobileMoney\src\Mpesa\Database\ModelEntities
 */
class MpesaC2bCallback extends Model
{
    protected $guarded = [];

    public function getNameAttribute()
    {
        return $this->FirstName . ' ' . $this->MiddleName . ' ' . $this->LastName;
    }
}
