<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillDenomination extends Model
{
    protected $fillable = ['bill_id', 'denomination', 'count'];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}