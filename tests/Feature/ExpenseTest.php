<?php

namespace Tests\Feature;

use App\Jobs\SendExpenseConfirmation;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_lists_expenses(): void
    {
        $this->withoutVite();
        Expense::factory()->create(['description' => 'Flat white']);

        $this->get('/')->assertOk()->assertSee('Flat white');
    }

    public function test_api_lists_expenses(): void
    {
        Expense::factory(3)->create();

        $this->getJson('/api/expenses')->assertOk()->assertJsonCount(3);
    }

    public function test_creating_an_expense_stores_receipt_and_queues_confirmation(): void
    {
        Queue::fake();
        Storage::fake();

        $response = $this->postJson('/api/expenses', [
            'spent_on' => '2026-10-19',
            'amount' => '12.50',
            'description' => 'Lunch',
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertCreated()->assertJsonPath('has_receipt', true);

        $expense = Expense::find($response->json('id'));
        Storage::assertExists($expense->receipt_path);
        Queue::assertPushed(SendExpenseConfirmation::class);

        $this->get("/api/expenses/{$expense->id}/receipt")->assertOk();
    }

    public function test_validation(): void
    {
        $this->postJson('/api/expenses', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['spent_on', 'amount', 'description']);
    }

    public function test_delete_removes_receipt(): void
    {
        Storage::fake();
        Storage::put('receipts/x.jpg', 'data');
        $expense = Expense::factory()->create(['receipt_path' => 'receipts/x.jpg']);

        $this->deleteJson("/api/expenses/{$expense->id}")->assertNoContent();

        $this->assertModelMissing($expense);
        Storage::assertMissing('receipts/x.jpg');
    }

    public function test_weekly_summary_writes_to_log(): void
    {
        Log::spy();
        Expense::factory()->create(['spent_on' => now()->toDateString(), 'amount' => 10]);

        $this->artisan('expenses:weekly-summary')->assertSuccessful();

        Log::shouldHaveReceived('info')->withArgs(fn ($msg) => $msg === 'Weekly expense summary');
    }
}
