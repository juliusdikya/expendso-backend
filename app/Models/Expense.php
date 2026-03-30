<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Wallet;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'note',
        'date'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
