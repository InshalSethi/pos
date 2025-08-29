<?php

namespace App\Listeners;

use App\Events\ExpenseApproved;
use App\Services\ExpenseAccountingService;
use Illuminate\Support\Facades\Log;

class CreateExpenseJournalEntry
{

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
    public function handle(ExpenseApproved $event): void
    {
        try {
            $this->accountingService->createExpenseJournalEntry($event->expense);

            Log::info('Journal entry created for approved expense', [
                'expense_id' => $event->expense->id,
                'expense_number' => $event->expense->expense_number,
                'amount' => $event->expense->amount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create journal entry for approved expense', [
                'expense_id' => $event->expense->id,
                'error' => $e->getMessage()
            ]);

            // Re-throw to trigger job retry if using queues
            throw $e;
        }
    }
}
