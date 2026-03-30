<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
