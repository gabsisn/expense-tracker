<?php

namespace App\Console\Commands;

use App\Models\Expense;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WeeklySummary extends Command
{
    protected $signature = 'expenses:weekly-summary {--weeks=1 : How many past weeks to summarise}';

    protected $description = 'Write a per-week expense total to the log';

    public function handle(): int
    {
        $from = Carbon::now()->startOfWeek()->subWeeks((int) $this->option('weeks'));

        $totals = Expense::where('spent_on', '>=', $from)
            ->get(['spent_on', 'amount'])
            ->groupBy(fn (Expense $e) => $e->spent_on->startOfWeek()->toDateString())
            ->map(fn ($rows) => [
                'count' => $rows->count(),
                'total' => number_format($rows->sum('amount'), 2, '.', ''),
            ])
            ->sortKeys();

        Log::info('Weekly expense summary', ['weeks' => $totals->all()]);

        $this->table(['Week starting', 'Count', 'Total'], $totals->map(fn ($t, $week) => [$week, $t['count'], $t['total']])->values());

        return self::SUCCESS;
    }
}
