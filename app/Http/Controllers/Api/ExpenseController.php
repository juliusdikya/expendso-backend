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

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|integer',
            'type' => 'required|in:expense,income',
            'date' => 'required|date',
        ]);

        $expenseCategories = [
            'food',
            'transport',
            'shopping',
            'bills',
            'entertainment',
            'health',
            'education',
            'other'
        ];

        $incomeCategories = [
            'salary',
            'freelance',
            'bonus',
            'investment',
            'gift',
            'other'
        ];

        if ($request->type === 'expense') {
            $request->validate([
                'category' => 'required|in:' . implode(',', $expenseCategories),
            ]);
        } else {
            $request->validate([
                'category' => 'required|in:' . implode(',', $incomeCategories),
            ]);
        }

        // check if wallet belongs to user
        $wallet = Wallet::where('id', $request->wallet_id)
            ->where('user_id', $this->userId())
            ->firstOrFail();

        // reduce / increase balance
        if ($request->type === 'expense') {
            if ($wallet->balance < $request->amount) {
                return response()->json([
                    'message' => 'Insufficient balance'
                ], 400);
            }

            $wallet->balance -= $request->amount;
        } else {
            $wallet->balance += $request->amount;
        }

        $wallet->save();

        return Expense::create([
            'user_id' => $this->userId(),
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'category' => $request->category,
            'type' => $request->type,
            'note' => $request->note,
            'date' => $request->date,
        ]);
    }

    public function total(Request $request)
    {
        $query = Expense::where('user_id', $this->userId());

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $totalIncome  = (clone $query)->where('type', 'income')->sum('amount');

        return response()->json([
            'total_expense' => $totalExpense,
            'total_income'  => $totalIncome,
            'total'         => $totalExpense, // Preserved 'total' returning total expenses for backward compatibility
        ]);
    }
}
