<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;

    protected $attributes = [
        "closed" => false,
    ];

    protected $fillable = [
        'billing_month',
        'closed',
    ];

    public function credit_details()
    {
        return $this->hasMany('App\Models\CreditDetail', );
    }
}
