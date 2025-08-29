<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
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
        // Register event listeners for expense accounting
        Event::listen(ExpenseApproved::class, CreateExpenseJournalEntry::class);
        Event::listen(ExpensePaid::class, CreateExpensePaymentJournalEntry::class);
        Event::listen(ExpenseRejected::class, ReverseExpenseJournalEntry::class);
    }
}
