<?php

namespace App\Http\Controllers;

use App\Jobs\SendExpenseConfirmation;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index()
    {
        return Expense::orderByDesc('spent_on')->orderByDesc('id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'spent_on' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'description' => ['required', 'string', 'max:255'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts');
        }

        $expense = Expense::create($data);

        SendExpenseConfirmation::dispatch($expense);

        return response()->json($expense, 201);
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_path) {
            Storage::delete($expense->receipt_path);
        }

        $expense->delete();

        return response()->noContent();
    }

    public function receipt(Expense $expense)
    {
        abort_unless($expense->receipt_path && Storage::exists($expense->receipt_path), 404);

        return Storage::download($expense->receipt_path);
    }
}
