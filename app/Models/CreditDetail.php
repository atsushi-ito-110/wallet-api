<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_id',
        'shop_id',
        'name',
        'billing_date',
        'amount',
    ];
}
