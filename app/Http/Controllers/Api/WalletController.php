<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{

    private function userId()
    {
        return auth()->id();
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
            'balance' => $request->balance ?? 0,
        ]);
    }

    public function topUp(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $wallet = Wallet::where('id', $id)
            ->where('user_id', $this->userId())
            ->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found or unauthorized'], 403);
        }

        $wallet->balance += $request->amount;
        $wallet->save();

        return response()->json ([
            'message' => 'Wallet topped up successfully',
            'wallet' => $wallet,
        ]);
    }
}

