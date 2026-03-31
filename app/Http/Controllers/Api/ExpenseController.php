<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Wallet;

class ExpenseController extends Controller
{
    private function userId()
    {
        return auth()->id();
    }

    public function index(Request $request)
    {
        $query = Expense::with('wallet')->where('user_id', $this->userId());

        if ($request->start_date && $request->end_date){
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|integer',
            'date' => 'required|date',
        ]);

        $wallet = Wallet::findOrFail($request->wallet_id);

        // check if wallet belongs to user
        $wallet = Wallet::where('id', $request->wallet_id)
        ->where('user_id', $this->userId())
        ->firstOrFail();

        // check if wallet has sufficient balance
        if ($wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient wallet balance'], 400);
        }

        // reduce balance
        $wallet->balance -= $request->amount;
        $wallet->save();

        return Expense::create([
            'user_id' => $this->userId(),
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'note' => $request->note,
            'date' => $request->date,
        ]);
    }

    public function total(Request $request)
    {
        $query = Expense::where('user_id', $this->userId());

        if ($request->start_date && $request->end_date){
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        return response()->json(['total' => $query->sum('amount')]);
    }
}
