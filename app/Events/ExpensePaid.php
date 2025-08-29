<?php

namespace App\Events;

use App\Models\Expense;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpensePaid
{
    use Dispatchable, SerializesModels;

    public Expense $expense;
    public ?int $bankAccountId;

    /**
     * Create a new event instance.
     */
    public function __construct(Expense $expense, ?int $bankAccountId = null)
    {
        $this->expense = $expense;
        $this->bankAccountId = $bankAccountId;
    }
}
