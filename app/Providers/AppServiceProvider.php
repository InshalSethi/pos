<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Events\ExpenseApproved;
use App\Events\ExpensePaid;
use App\Events\ExpenseRejected;
use App\Listeners\CreateExpenseJournalEntry;
use App\Listeners\CreateExpensePaymentJournalEntry;
use App\Listeners\ReverseExpenseJournalEntry;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "admin", "owner" roles and current company owner all permissions
        Gate::before(function ($user, $ability) {
            if ($user->hasRole(['admin', 'owner', 'super-admin']) || (int)$user->id === 1) {
                return true;
            }
            if ($user->currentCompany && (int)$user->id === (int)$user->currentCompany->user_id) {
                return true;
            }
            return null;
        });

        // Enforce morph map for polymorphic relationships
        Relation::enforceMorphMap([
            'customer' => \App\Models\Customer::class,
            'supplier' => \App\Models\Supplier::class,
            'user' => \App\Models\User::class,
            'admin' => \App\Models\Admin::class,
            'sale' => \App\Models\Sale::class,
            'purchase' => \App\Models\PurchaseOrder::class,
            'expense' => \App\Models\Expense::class,
            'payment' => \App\Models\Payment::class,
            'payment_receipt' => \App\Models\PaymentReceipt::class,
        ]);

        // Register event listeners for expense accounting
        Event::listen(ExpenseApproved::class, CreateExpenseJournalEntry::class);
        Event::listen(ExpensePaid::class, CreateExpensePaymentJournalEntry::class);
        Event::listen(ExpenseRejected::class, ReverseExpenseJournalEntry::class);

        // Register ProductObserver and ProductVariationObserver for automatic Opening Stock journal entries
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\ProductVariation::observe(\App\Observers\ProductVariationObserver::class);

        // Register FBR Observers for automated FBR fiscalization
        \App\Models\Sale::observe(\App\Observers\SaleObserver::class);
        \App\Models\PurchaseOrder::observe(\App\Observers\PurchaseOrderObserver::class);
        \App\Models\Transaction::observe(\App\Observers\TransactionObserver::class);
        \App\Models\PaymentReceipt::observe(\App\Observers\PaymentReceiptObserver::class);
    }
}
