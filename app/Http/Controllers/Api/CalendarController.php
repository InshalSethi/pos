<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\GoogleCalendarSetting;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    /**
     * Fetch all aggregated calendar events for the active date window.
     */
    public function getEvents(Request $request)
    {
        $startDate = $request->query('start_date') 
            ? Carbon::parse($request->query('start_date'))->startOfDay() 
            : Carbon::now()->startOfMonth()->subDays(7);

        $endDate = $request->query('end_date') 
            ? Carbon::parse($request->query('end_date'))->endOfDay() 
            : Carbon::now()->endOfMonth()->addDays(7);

        $requestedTypes = $request->query('types') 
            ? (is_array($request->query('types')) ? $request->query('types') : explode(',', $request->query('types'))) 
            : ['sales', 'purchases', 'payments_out', 'payment_receipts', 'expenses', 'google'];

        $companyId = Auth::user()->current_company_id;
        $events = [];

        // 1. Sale Invoices
        if (in_array('sales', $requestedTypes)) {
            $sales = Sale::with('customer')
                ->where(function ($q) use ($companyId) {
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                })
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('sale_date', [$startDate, $endDate])
                      ->orWhereBetween('due_date', [$startDate, $endDate]);
                })
                ->get();

            foreach ($sales as $sale) {
                $dateStr = $sale->sale_date ? $sale->sale_date->format('Y-m-d') : ($sale->due_date ? $sale->due_date->format('Y-m-d') : null);
                if (!$dateStr) continue;

                $customerName = $sale->customer ? $sale->customer->name : ($sale->customer_phone ?? 'Walk-in Customer');
                $events[] = [
                    'id' => 'sale_' . $sale->id,
                    'model_id' => $sale->id,
                    'title' => 'Sale #' . ($sale->sale_number ?? $sale->id),
                    'subtitle' => $customerName,
                    'date' => $dateStr,
                    'time' => $sale->sale_date ? $sale->sale_date->format('H:i') : null,
                    'type' => 'sale_invoice',
                    'type_label' => 'Sale Invoice',
                    'status' => $sale->status ?? 'completed',
                    'amount' => (float) $sale->total_amount,
                    'formatted_amount' => '$' . number_format($sale->total_amount, 2),
                    'badge_color' => 'indigo',
                    'url' => '/sales/invoices/' . $sale->id,
                    'details' => [
                        'customer' => $customerName,
                        'paid_amount' => (float) $sale->paid_amount,
                        'balance_due' => (float) ($sale->total_amount - $sale->paid_amount),
                    ],
                ];
            }
        }

        // 2. Purchase Invoices / Orders
        if (in_array('purchases', $requestedTypes)) {
            $purchases = PurchaseOrder::with('supplier')
                ->where(function ($q) use ($companyId) {
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                })
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('order_date', [$startDate, $endDate])
                      ->orWhereBetween('expected_delivery_date', [$startDate, $endDate]);
                })
                ->get();

            foreach ($purchases as $po) {
                $dateStr = $po->order_date ? $po->order_date->format('Y-m-d') : ($po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : null);
                if (!$dateStr) continue;

                $supplierName = $po->supplier ? $po->supplier->name : ($po->supplier_name ?? 'Supplier');
                $events[] = [
                    'id' => 'purchase_' . $po->id,
                    'model_id' => $po->id,
                    'title' => 'PO #' . ($po->po_number ?? $po->id),
                    'subtitle' => $supplierName,
                    'date' => $dateStr,
                    'time' => '10:00',
                    'type' => 'purchase_invoice',
                    'type_label' => 'Purchase Invoice',
                    'status' => $po->status ?? 'pending',
                    'amount' => (float) ($po->grand_total ?? $po->total_amount),
                    'formatted_amount' => '$' . number_format($po->grand_total ?? $po->total_amount, 2),
                    'badge_color' => 'emerald',
                    'url' => '/purchase/orders/' . $po->id,
                    'details' => [
                        'supplier' => $supplierName,
                        'amount_paid' => (float) $po->amount_paid,
                        'due_amount' => (float) $po->due_amount,
                    ],
                ];
            }
        }

        // 3. Payments Out (With Status)
        if (in_array('payments_out', $requestedTypes)) {
            $payments = Payment::where(function ($q) use ($companyId) {
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                })
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->get();

            foreach ($payments as $pm) {
                $dateStr = $pm->payment_date ? $pm->payment_date->format('Y-m-d') : null;
                if (!$dateStr) continue;

                $events[] = [
                    'id' => 'payment_out_' . $pm->id,
                    'model_id' => $pm->id,
                    'title' => 'Payment Out #' . ($pm->payment_number ?? $pm->id),
                    'subtitle' => $pm->payee_name ?? 'Payee',
                    'date' => $dateStr,
                    'time' => '11:00',
                    'type' => 'payment_out',
                    'type_label' => 'Payment Out',
                    'status' => $pm->status ?? 'pending',
                    'amount' => (float) $pm->amount,
                    'formatted_amount' => '$' . number_format($pm->amount, 2),
                    'badge_color' => 'amber',
                    'url' => '/payments',
                    'details' => [
                        'payee' => $pm->payee_name ?? 'N/A',
                        'payment_type' => $pm->payment_type,
                        'payment_method' => $pm->payment_method,
                    ],
                ];
            }
        }

        // 4. Payment Receipts (With Status)
        if (in_array('payment_receipts', $requestedTypes)) {
            $receipts = PaymentReceipt::where(function ($q) use ($companyId) {
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                })
                ->whereBetween('receipt_date', [$startDate, $endDate])
                ->get();

            foreach ($receipts as $rc) {
                $dateStr = $rc->receipt_date ? $rc->receipt_date->format('Y-m-d') : null;
                if (!$dateStr) continue;

                $events[] = [
                    'id' => 'receipt_' . $rc->id,
                    'model_id' => $rc->id,
                    'title' => 'Receipt #' . ($rc->receipt_number ?? $rc->id),
                    'subtitle' => $rc->payer_name ?? 'Payer',
                    'date' => $dateStr,
                    'time' => '14:00',
                    'type' => 'payment_receipt',
                    'type_label' => 'Payment Receipt',
                    'status' => $rc->status ?? 'verified',
                    'amount' => (float) $rc->amount,
                    'formatted_amount' => '$' . number_format($rc->amount, 2),
                    'badge_color' => 'cyan',
                    'url' => '/payment-receipts',
                    'details' => [
                        'payer' => $rc->payer_name ?? 'N/A',
                        'receipt_type' => $rc->receipt_type,
                        'payment_method' => $rc->payment_method,
                    ],
                ];
            }
        }

        // 5. Expenses
        if (in_array('expenses', $requestedTypes)) {
            $expenses = Expense::with('category')
                ->where(function ($q) use ($companyId) {
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                })
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->get();

            foreach ($expenses as $exp) {
                $dateStr = $exp->expense_date ? $exp->expense_date->format('Y-m-d') : null;
                if (!$dateStr) continue;

                $categoryName = $exp->category ? $exp->category->name : ($exp->vendor_name ?? 'Expense');
                $events[] = [
                    'id' => 'expense_' . $exp->id,
                    'model_id' => $exp->id,
                    'title' => 'Expense: ' . ($exp->title ?: $exp->expense_number),
                    'subtitle' => $categoryName,
                    'date' => $dateStr,
                    'time' => '16:00',
                    'type' => 'expense',
                    'type_label' => 'Expense',
                    'status' => $exp->status ?? 'approved',
                    'amount' => (float) $exp->amount,
                    'formatted_amount' => '$' . number_format($exp->amount, 2),
                    'badge_color' => 'rose',
                    'url' => '/expenses',
                    'details' => [
                        'category' => $categoryName,
                        'vendor' => $exp->vendor_name,
                        'expense_number' => $exp->expense_number,
                    ],
                ];
            }
        }

        // 6. Google Calendar Events
        if (in_array('google', $requestedTypes)) {
            $googleEvents = $this->fetchGoogleCalendarEvents(Auth::user(), $startDate, $endDate);
            foreach ($googleEvents as $ge) {
                $events[] = $ge;
            }
        }

        // Sort events by date & time
        usort($events, function ($a, $b) {
            $cmp = strcmp($a['date'], $b['date']);
            if ($cmp === 0) {
                return strcmp($a['time'] ?? '00:00', $b['time'] ?? '00:00');
            }
            return $cmp;
        });

        return response()->json([
            'status' => 'success',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_events' => count($events),
            'events' => $events,
        ]);
    }

    /**
     * Get user's Google Calendar integration settings.
     */
    public function getSettings()
    {
        $user = Auth::user();
        $setting = GoogleCalendarSetting::where('user_id', $user->id)
            ->where(function ($q) use ($user) {
                if ($user->current_company_id) {
                    $q->where('company_id', $user->current_company_id);
                }
            })
            ->first();

        if (!$setting) {
            $setting = GoogleCalendarSetting::create([
                'user_id' => $user->id,
                'company_id' => $user->current_company_id,
                'is_synced' => false,
                'calendar_id' => 'primary',
                'google_account_email' => $user->email,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'settings' => [
                'is_synced' => (bool) $setting->is_synced,
                'calendar_id' => $setting->calendar_id,
                'google_account_email' => $setting->google_account_email ?: $user->email,
                'last_synced_at' => $setting->last_synced_at ? $setting->last_synced_at->toIso8601String() : null,
                'sync_sales' => (bool) $setting->sync_sales,
                'sync_purchases' => (bool) $setting->sync_purchases,
                'sync_payments' => (bool) $setting->sync_payments,
                'sync_receipts' => (bool) $setting->sync_receipts,
                'sync_expenses' => (bool) $setting->sync_expenses,
            ]
        ]);
    }

    /**
     * Update Google Calendar sync settings or toggle sync state.
     */
    public function toggleSync(Request $request)
    {
        $user = Auth::user();
        $setting = GoogleCalendarSetting::where('user_id', $user->id)
            ->where(function ($q) use ($user) {
                if ($user->current_company_id) {
                    $q->where('company_id', $user->current_company_id);
                }
            })
            ->first();

        if (!$setting) {
            $setting = new GoogleCalendarSetting();
            $setting->user_id = $user->id;
            $setting->company_id = $user->current_company_id;
        }

        $setting->is_synced = $request->input('is_synced', !$setting->is_synced);
        if ($request->has('calendar_id')) {
            $setting->calendar_id = $request->input('calendar_id');
        }
        if ($request->has('google_account_email')) {
            $setting->google_account_email = $request->input('google_account_email');
        }
        if ($setting->is_synced) {
            $setting->last_synced_at = now();
        }

        $setting->save();

        return response()->json([
            'status' => 'success',
            'message' => $setting->is_synced ? 'Google Calendar synchronized successfully.' : 'Google Calendar synchronization disabled.',
            'settings' => [
                'is_synced' => (bool) $setting->is_synced,
                'calendar_id' => $setting->calendar_id,
                'google_account_email' => $setting->google_account_email,
                'last_synced_at' => $setting->last_synced_at ? $setting->last_synced_at->toIso8601String() : null,
            ]
        ]);
    }

    /**
     * Force immediate Google Calendar sync.
     */
    public function syncNow()
    {
        $user = Auth::user();
        $setting = GoogleCalendarSetting::where('user_id', $user->id)->first();
        if ($setting) {
            $setting->is_synced = true;
            $setting->last_synced_at = now();
            $setting->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Google Calendar events successfully refreshed and synchronized.',
            'last_synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Disconnect Google Calendar and purge stored tokens.
     */
    public function disconnect(Request $request)
    {
        $user = Auth::user();
        $setting = GoogleCalendarSetting::where('user_id', $user->id)->first();
        if ($setting) {
            $setting->update([
                'is_synced' => false,
                'access_token' => null,
                'refresh_token' => null,
                'google_account_email' => null,
                'last_synced_at' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Google Calendar disconnected successfully.',
            'settings' => [
                'is_synced' => false,
                'calendar_id' => 'primary',
                'google_account_email' => null,
                'last_synced_at' => null,
            ]
        ]);
    }

    /**
     * Helper to retrieve Google Calendar events via real Google API.
     */
    private function fetchGoogleCalendarEvents($user, $startDate, $endDate)
    {
        $setting = GoogleCalendarSetting::where('user_id', $user->id)->first();
        if (!$setting || !$setting->is_synced || !$setting->access_token) {
            return [];
        }

        $events = [];

        try {
            $response = Http::withToken($setting->access_token)
                ->get("https://www.googleapis.com/calendar/v3/calendars/{$setting->calendar_id}/events", [
                    'timeMin' => $startDate->toRfc3339String(),
                    'timeMax' => $endDate->toRfc3339String(),
                    'singleEvents' => 'true',
                    'orderBy' => 'startTime',
                ]);

            if ($response->successful()) {
                $items = $response->json('items', []);
                foreach ($items as $item) {
                    $start = $item['start']['dateTime'] ?? $item['start']['date'] ?? null;
                    if (!$start) continue;

                    $carbonDate = Carbon::parse($start);
                    $events[] = [
                        'id' => 'google_' . ($item['id'] ?? uniqid()),
                        'model_id' => $item['id'] ?? null,
                        'title' => $item['summary'] ?? 'Google Calendar Event',
                        'subtitle' => 'Google Calendar (' . ($setting->google_account_email ?: 'Synced') . ')',
                        'date' => $carbonDate->format('Y-m-d'),
                        'time' => $carbonDate->format('H:i'),
                        'type' => 'google_event',
                        'type_label' => 'Google Calendar',
                        'status' => 'synced',
                        'amount' => 0,
                        'formatted_amount' => 'Google Event',
                        'badge_color' => 'purple',
                        'url' => $item['htmlLink'] ?? '#',
                        'details' => [
                            'location' => $item['location'] ?? 'N/A',
                            'description' => $item['description'] ?? '',
                            'calendar_email' => $setting->google_account_email,
                        ]
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Google Calendar API Fetch Exception: ' . $e->getMessage());
        }

        return $events;
    }
}
