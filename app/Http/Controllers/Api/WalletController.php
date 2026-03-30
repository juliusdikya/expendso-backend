<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{

    private function userId()
    {
        return 1;
    }

    public function index()
    {
        return Wallet::where('user_id', $this->userId())->get();
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    return Wallet::create([
        'user_id' => $this->userId(),
        'name' => $request->name,
    ]);
}
}

