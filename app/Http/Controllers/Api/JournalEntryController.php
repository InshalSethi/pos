<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JournalEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accounting.view')->only(['index', 'show']);
        $this->middleware('permission:accounting.create')->only(['store']);
        $this->middleware('permission:accounting.edit')->only(['update', 'post', 'reverse']);
        $this->middleware('permission:accounting.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = JournalEntry::with(['createdBy', 'postedBy', 'journalEntryLines.account']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by entry type
        if ($request->has('entry_type')) {
            $query->where('entry_type', $request->entry_type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('entry_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('entry_date', '<=', $request->end_date);
        }

        // Search by entry number or description
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $journalEntries = $query->orderBy('entry_date', 'desc')
                               ->orderBy('entry_number', 'desc')
                               ->paginate($request->get('per_page', 15));

        return response()->json($journalEntries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'description' => 'required|string',
            'entry_type' => 'required|in:manual,automatic,adjustment,closing',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit_amount' => 'required|numeric|min:0',
            'lines.*.credit_amount' => 'required|numeric|min:0',
            'lines.*.partner_type' => 'nullable|string',
            'lines.*.partner_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate that each line has either debit or credit (not both, not neither)
        foreach ($request->lines as $index => $line) {
            $hasDebit = $line['debit_amount'] > 0;
            $hasCredit = $line['credit_amount'] > 0;

            if ($hasDebit && $hasCredit) {
                return response()->json([
                    'message' => "Line " . ($index + 1) . " cannot have both debit and credit amounts"
                ], 422);
            }

            if (!$hasDebit && !$hasCredit) {
                return response()->json([
                    'message' => "Line " . ($index + 1) . " must have either debit or credit amount"
                ], 422);
            }
        }

        // Validate that total debits equal total credits
        $totalDebits = collect($request->lines)->sum('debit_amount');
        $totalCredits = collect($request->lines)->sum('credit_amount');

        if (abs($totalDebits - $totalCredits) > 0.01) {
            return response()->json([
                'message' => 'Total debits must equal total credits'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate entry number
            $entryNumber = 'JE-' . date('Ymd') . '-' . str_pad(JournalEntry::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $request->entry_date,
                'reference' => $request->reference,
                'description' => $request->description,
                'entry_type' => $request->entry_type,
                'status' => 'draft',
                'total_debit' => $totalDebits,
                'total_credit' => $totalCredits,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Create journal entry lines
            foreach ($request->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'],
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'partner_type' => $line['partner_type'] ?? null,
                    'partner_id' => $line['partner_id'] ?? null,
                ]);
            }

            DB::commit();

            $journalEntry->load(['createdBy', 'journalEntryLines.account']);

            return response()->json([
                'message' => 'Journal entry created successfully',
                'journal_entry' => $journalEntry
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create journal entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JournalEntry $journalEntry): JsonResponse
    {
        $journalEntry->load(['createdBy', 'postedBy', 'journalEntryLines.account']);

        return response()->json($journalEntry);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        // Can only edit draft entries
        if ($journalEntry->status !== 'draft') {
            return response()->json([
                'message' => 'Can only edit draft journal entries'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'description' => 'required|string',
            'entry_type' => 'required|in:manual,automatic,adjustment,closing',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit_amount' => 'required|numeric|min:0',
            'lines.*.credit_amount' => 'required|numeric|min:0',
            'lines.*.partner_type' => 'nullable|string',
            'lines.*.partner_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate line amounts
        foreach ($request->lines as $index => $line) {
            $hasDebit = $line['debit_amount'] > 0;
            $hasCredit = $line['credit_amount'] > 0;

            if ($hasDebit && $hasCredit) {
                return response()->json([
                    'message' => "Line " . ($index + 1) . " cannot have both debit and credit amounts"
                ], 422);
            }

            if (!$hasDebit && !$hasCredit) {
                return response()->json([
                    'message' => "Line " . ($index + 1) . " must have either debit or credit amount"
                ], 422);
            }
        }

        // Validate totals
        $totalDebits = collect($request->lines)->sum('debit_amount');
        $totalCredits = collect($request->lines)->sum('credit_amount');

        if (abs($totalDebits - $totalCredits) > 0.01) {
            return response()->json([
                'message' => 'Total debits must equal total credits'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update journal entry
            $journalEntry->update([
                'entry_date' => $request->entry_date,
                'reference' => $request->reference,
                'description' => $request->description,
                'entry_type' => $request->entry_type,
                'total_debit' => $totalDebits,
                'total_credit' => $totalCredits,
            ]);

            // Delete existing lines and recreate
            $journalEntry->journalEntryLines()->delete();

            foreach ($request->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'],
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'partner_type' => $line['partner_type'] ?? null,
                    'partner_id' => $line['partner_id'] ?? null,
                ]);
            }

            DB::commit();

            $journalEntry->load(['createdBy', 'journalEntryLines.account']);

            return response()->json([
                'message' => 'Journal entry updated successfully',
                'journal_entry' => $journalEntry
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update journal entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JournalEntry $journalEntry): JsonResponse
    {
        // Can only delete draft entries
        if ($journalEntry->status !== 'draft') {
            return response()->json([
                'message' => 'Can only delete draft journal entries'
            ], 422);
        }

        $journalEntry->delete();

        return response()->json([
            'message' => 'Journal entry deleted successfully'
        ]);
    }

    /**
     * Post a journal entry
     */
    public function post(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->status !== 'draft') {
            return response()->json([
                'message' => 'Can only post draft journal entries'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update journal entry status
            $journalEntry->update([
                'status' => 'posted',
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            // Update account balances
            foreach ($journalEntry->journalEntryLines as $line) {
                $account = Account::find($line->account_id);
                $account->updateCurrentBalance();
            }

            DB::commit();

            $journalEntry->load(['createdBy', 'postedBy', 'journalEntryLines.account']);

            return response()->json([
                'message' => 'Journal entry posted successfully',
                'journal_entry' => $journalEntry
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to post journal entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverse a journal entry
     */
    public function reverse(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->status !== 'posted') {
            return response()->json([
                'message' => 'Can only reverse posted journal entries'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
            'reversal_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create reversal entry
            $reversalNumber = 'REV-' . $journalEntry->entry_number;

            $reversalEntry = JournalEntry::create([
                'entry_number' => $reversalNumber,
                'entry_date' => $request->reversal_date,
                'reference' => $journalEntry->reference,
                'description' => 'REVERSAL: ' . $journalEntry->description . ' - ' . $request->reason,
                'entry_type' => 'adjustment',
                'status' => 'posted',
                'total_debit' => $journalEntry->total_credit,
                'total_credit' => $journalEntry->total_debit,
                'created_by' => auth()->id() ?? 1,
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now(),
            ]);

            // Create reversal lines (swap debits and credits)
            foreach ($journalEntry->journalEntryLines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $line->account_id,
                    'description' => 'REVERSAL: ' . $line->description,
                    'debit_amount' => $line->credit_amount,
                    'credit_amount' => $line->debit_amount,
                    'partner_type' => $line->partner_type,
                    'partner_id' => $line->partner_id,
                ]);
            }

            // Update original entry status
            $journalEntry->update(['status' => 'reversed']);

            // Update account balances
            foreach ($reversalEntry->journalEntryLines as $line) {
                $account = Account::find($line->account_id);
                $account->updateCurrentBalance();
            }

            DB::commit();

            $reversalEntry->load(['createdBy', 'postedBy', 'journalEntryLines.account']);

            return response()->json([
                'message' => 'Journal entry reversed successfully',
                'reversal_entry' => $reversalEntry,
                'original_entry' => $journalEntry->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to reverse journal entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
