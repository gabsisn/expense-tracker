<?php

namespace App\Jobs;

use App\Models\Expense;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendExpenseConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Expense $expense) {}

    public function handle(): void
    {
        $e = $this->expense;

        Mail::raw(
            "Expense recorded: {$e->description} — {$e->amount} on {$e->spent_on->toDateString()}",
            fn ($message) => $message->to(config('mail.from.address'))->subject("Expense #{$e->id} recorded"),
        );

        Log::info('Expense confirmation sent', ['expense_id' => $e->id]);
    }
}
