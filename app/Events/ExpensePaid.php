<?php

namespace App\Events;

use App\Models\Expense;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpensePaid
{
    use Dispatchable, SerializesModels;

    public Expense $expense;

    /**
     * Create a new event instance.
     */
    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
    }
}
