<?php

namespace App\Listeners;

use App\Events\ExpensePaid;
use App\Services\ExpenseAccountingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateExpensePaymentJournalEntry implements ShouldQueue
{
    use InteractsWithQueue;

    private ExpenseAccountingService $accountingService;

    /**
     * Create the event listener.
     */
    public function __construct(ExpenseAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Handle the event.
     */
    public function handle(ExpensePaid $event): void
    {
        try {
            $journalEntry = $this->accountingService->createExpensePaymentJournalEntry($event->expense);

            if ($journalEntry) {
                Log::info('Payment journal entry created for expense', [
                    'expense_id' => $event->expense->id,
                    'expense_number' => $event->expense->expense_number,
                    'amount' => $event->expense->amount,
                    'journal_entry_id' => $journalEntry->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create payment journal entry for expense', [
                'expense_id' => $event->expense->id,
                'error' => $e->getMessage()
            ]);

            // Re-throw to trigger job retry if using queues
            throw $e;
        }
    }
}
