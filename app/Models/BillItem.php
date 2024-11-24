<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    protected $fillable = ['bill_id', 'product_id', 'quantity','unit_price','tax_percentage'];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}