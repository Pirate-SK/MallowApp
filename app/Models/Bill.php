<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = ['customer_email', 'amount_paid'];

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function denominations()
    {
        return $this->hasMany(BillDenomination::class);
    }
}