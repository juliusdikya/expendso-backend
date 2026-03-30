<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    private function userId()
    {
        return 1;
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
        'note' => 'nullable|string|max:255',
        'date' => 'required|date',
    ]);

    return Expense::create([
        'user_id' => $this->userId(),
        'wallet_id' => $request->wallet_id,
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

        return ['total' => $query->sum('amount')];
    }
}
