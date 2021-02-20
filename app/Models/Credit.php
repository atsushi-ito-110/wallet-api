<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;
    public function credit_details()
    {
        return $this->hasMany('App\CreditDetail');
    }
}
