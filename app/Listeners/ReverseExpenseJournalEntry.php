<?php

namespace App\Listeners;

use App\Events\ExpenseRejected;
use App\Services\ExpenseAccountingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ReverseExpenseJournalEntry implements ShouldQueue
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
    public function handle(ExpenseRejected $event): void
    {
        try {
            $reversalEntry = $this->accountingService->reverseExpenseJournalEntry($event->expense);

            if ($reversalEntry) {
                Log::info('Journal entry reversed for rejected expense', [
                    'expense_id' => $event->expense->id,
                    'expense_number' => $event->expense->expense_number,
                    'reversal_entry_id' => $reversalEntry->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to reverse journal entry for rejected expense', [
                'expense_id' => $event->expense->id,
                'error' => $e->getMessage()
            ]);

            // Re-throw to trigger job retry if using queues
            throw $e;
        }
    }
}
