<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\DoubleEntryAccountingService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    protected DoubleEntryAccountingService $accountingService;

    public function __construct(DoubleEntryAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Handle the Product "created" event.
     * Triggers automatic double-entry journal entry for initial opening stock.
     */
    public function created(Product $product): void
    {
        try {
            $this->accountingService->createOpeningStockEntry($product);
        } catch (\Throwable $e) {
            Log::error("Failed creating Opening Stock Journal Entry for Product #{$product->id}: " . $e->getMessage(), [
                'product_id' => $product->id,
                'exception' => $e
            ]);
        }
    }

    /**
     * Handle the Product "deleted" event.
     * Reverses and deletes linked opening stock and adjustment journal entries for unsold products.
     */
    public function deleted(Product $product): void
    {
        try {
            $companyId = $product->company_id ?: (auth()->user()?->current_company_id ?? 1);

            // Locate all linked opening stock and direct stock update journal entries for this product
            $entries = \App\Models\JournalEntry::where('company_id', $companyId)
                ->where(function ($q) use ($product) {
                    $q->where(function ($s) use ($product) {
                        $s->where('source_type', 'product_opening_stock')
                          ->where('source_id', $product->id);
                    })->orWhere('reference', 'ITEM-UPDATE-' . $product->id);
                })
                ->get();

            $affectedAccountIds = [];

            foreach ($entries as $je) {
                foreach ($je->journalEntryLines as $line) {
                    if ($line->account_id) {
                        $affectedAccountIds[] = $line->account_id;
                    }
                }
                \App\Models\JournalEntryLine::where('journal_entry_id', $je->id)->delete();
                $je->delete();
            }

            // Recalculate ground-truth balances for all affected COA accounts
            foreach (array_unique($affectedAccountIds) as $accId) {
                $acc = \App\Models\Account::find($accId);
                if ($acc) {
                    $acc->updateCurrentBalance();
                }
            }
        } catch (\Throwable $e) {
            Log::error("Failed cleaning up Journal Entries for deleted Product #{$product->id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Product "restored" event.
     * Re-creates initial opening stock journal entry and updates COA balances when a product is restored.
     */
    public function restored(Product $product): void
    {
        try {
            $this->accountingService->createOpeningStockEntry($product);
        } catch (\Throwable $e) {
            Log::error("Failed re-creating Opening Stock Journal Entry for restored Product #{$product->id}: " . $e->getMessage());
        }
    }
}
