<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer General Ledger - {{ $customer->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
        }

        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin: 4px 0 0 0;
        }

        .meta-text {
            font-size: 10px;
            color: #64748b;
        }

        .customer-info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .customer-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customer-info-table td {
            padding: 3px 5px;
            vertical-align: top;
            font-size: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
        }

        .info-value {
            color: #0f172a;
            font-weight: bold;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .stat-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }

        .stat-card.pending {
            border-left: 4px solid #f59e0b;
            background-color: #fffbeb;
        }

        .stat-card.received {
            border-left: 4px solid #10b981;
            background-color: #ecfdf5;
        }

        .stat-card.balance {
            border-left: 4px solid #6366f1;
            background-color: #eef2ff;
        }

        .stat-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 3px;
        }

        .stat-amount {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .section-header {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e293b;
            margin-top: 20px;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #cbd5e1;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }

        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            color: #1e293b;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-paid { background-color: #d1fae5; color: #065f46; }
        .badge-partial { background-color: #fef3c7; color: #92400e; }
        .badge-unpaid { background-color: #fee2e2; color: #991b1b; }
        .badge-income { background-color: #d1fae5; color: #065f46; }
        .badge-expense { background-color: #fee2e2; color: #991b1b; }

        .text-debit { color: #dc2626; font-weight: bold; }
        .text-credit { color: #16a34a; font-weight: bold; }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td>
                <div class="company-title">{{ config('app.name', 'POS System') }}</div>
                <div class="report-title">CUSTOMER GENERAL LEDGER STATEMENT</div>
            </td>
            <td class="text-right" style="vertical-align: bottom;">
                <div class="meta-text"><strong>Period:</strong> 
                    @if($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    @else
                        All Time
                    @endif
                </div>
                <div class="meta-text"><strong>Generated Date:</strong> {{ now()->format('M d, Y g:i A') }}</div>
            </td>
        </tr>
    </table>

    <!-- Customer Basic Information -->
    <div class="customer-info-box">
        <table class="customer-info-table">
            <tr>
                <td style="width: 33%;">
                    <div class="info-label">Customer Name</div>
                    <div class="info-value">{{ $customer->name }}</div>
                </td>
                <td style="width: 33%;">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $customer->email ?: 'N/A' }}</div>
                </td>
                <td style="width: 34%;">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">{{ $customer->phone ?: 'N/A' }}</div>
                </td>
            </tr>
            <tr>
                <td style="margin-top: 5px;">
                    <div class="info-label">Customer ID</div>
                    <div class="info-value">#{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</div>
                </td>
                <td style="margin-top: 5px;">
                    <div class="info-label">Customer Type</div>
                    <div class="info-value">{{ ucfirst(str_replace('_', ' ', $customer->type ?: 'registered')) }}</div>
                </td>
                <td style="margin-top: 5px;">
                    <div class="info-label">Wallet Advance Balance</div>
                    <div class="info-value" style="color: #059669;">${{ number_format((float)($customer->wallet_balance ?? 0), 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Stats Cards -->
    <table class="stats-table">
        <tr>
            <td style="width: 32%; padding-right: 8px;">
                <div class="stat-card pending">
                    <div class="stat-label">Payment Pending</div>
                    <div class="stat-amount" style="color: #b45309;">${{ number_format($paymentPending, 2) }}</div>
                </div>
            </td>
            <td style="width: 32%; padding-right: 8px;">
                <div class="stat-card received">
                    <div class="stat-label">Payment Received</div>
                    <div class="stat-amount" style="color: #047857;">${{ number_format($paymentReceived, 2) }}</div>
                </div>
            </td>
            <td style="width: 36%;">
                <div class="stat-card balance">
                    <div class="stat-label">Closing Balance</div>
                    <div class="stat-amount" style="color: #4338ca;">${{ number_format($closingBalance, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- SECTION 1: Sale Invoices -->
    <div class="section-header">Sale Invoices</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Invoice #</th>
                <th style="width: 13%;">Date</th>
                <th style="width: 20%;">Salesman</th>
                <th style="width: 12%;" class="text-center">Status</th>
                <th style="width: 12%;" class="text-center">Method</th>
                <th style="width: 14%;" class="text-right">Total</th>
                <th style="width: 14%;" class="text-right">Paid</th>
                <th style="width: 14%;" class="text-right">Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                @php
                    $paid = (float) $sale->paid_amount;
                    $total = (float) $sale->total_amount;
                    $due = max(0, $total - $paid);
                    $status = 'UNPAID';
                    $statusClass = 'badge-unpaid';
                    if ($paid >= $total && $total > 0) {
                        $status = 'PAID';
                        $statusClass = 'badge-paid';
                    } elseif ($paid > 0 && $paid < $total) {
                        $status = 'PARTIAL';
                        $statusClass = 'badge-partial';
                    }
                @endphp
                <tr>
                    <td style="font-weight: bold; color: #4338ca;">{{ $sale->sale_number }}</td>
                    <td>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $sale->salesman ? $sale->salesman->name : ($sale->user ? $sale->user->name : 'N/A') }}</td>
                    <td class="text-center"><span class="badge {{ $statusClass }}">{{ $status }}</span></td>
                    <td class="text-center" style="text-transform: uppercase;">{{ $sale->payment_method ?: 'Cash' }}</td>
                    <td class="text-right" style="font-weight: bold;">${{ number_format($total, 2) }}</td>
                    <td class="text-right text-credit">${{ number_format($paid, 2) }}</td>
                    <td class="text-right text-debit">${{ number_format($due, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #94a3b8; font-style: italic;">
                        No sale invoices found for this customer in selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SECTION 2: Sale Returns -->
    <div class="section-header">Sale Returns</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">Return #</th>
                <th style="width: 14%;">Date</th>
                <th style="width: 20%;">Original Invoice</th>
                <th style="width: 15%;" class="text-center">Refund Method</th>
                <th style="width: 15%;" class="text-right">Refund Amount</th>
                <th style="width: 18%;">Notes / Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returns as $ret)
                <tr>
                    <td style="font-weight: bold; color: #dc2626;">{{ $ret->sale_number }}</td>
                    <td>{{ $ret->sale_date ? \Carbon\Carbon::parse($ret->sale_date)->format('M d, Y') : 'N/A' }}</td>
                    <td style="color: #4338ca; font-weight: bold;">{{ $ret->original_sale_number ?: 'N/A' }}</td>
                    <td class="text-center" style="text-transform: uppercase;">{{ $ret->payment_method ?: 'Store Credit' }}</td>
                    <td class="text-right text-debit">${{ number_format((float)$ret->total_amount, 2) }}</td>
                    <td>{{ $ret->notes ?: 'No reason specified' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #94a3b8; font-style: italic;">
                        No sale returns recorded for this customer in selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SECTION 3: Transactions -->
    <div class="section-header">Transactions</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 16%;">Reference</th>
                <th style="width: 28%;">Description</th>
                <th style="width: 14%;" class="text-center">Type</th>
                <th style="width: 10%;" class="text-right">Debit ($)</th>
                <th style="width: 10%;" class="text-right">Credit ($)</th>
                <th style="width: 10%;" class="text-right">Balance ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td>{{ $tx['date'] ? \Carbon\Carbon::parse($tx['date'])->format('M d, Y') : 'N/A' }}</td>
                    <td style="font-weight: bold;">{{ $tx['reference'] }}</td>
                    <td>{{ $tx['description'] }}</td>
                    <td class="text-center">
                        <span class="badge {{ $tx['credit'] > 0 ? 'badge-income' : 'badge-expense' }}">
                            {{ $tx['type'] }}
                        </span>
                    </td>
                    <td class="text-right">
                        @if($tx['debit'] > 0)
                            <span class="text-debit">${{ number_format($tx['debit'], 2) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if($tx['credit'] > 0)
                            <span class="text-credit">${{ number_format($tx['credit'], 2) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        ${{ number_format($tx['running_balance'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #94a3b8; font-style: italic;">
                        No payment transactions found for this customer in selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        This document is an official financial ledger statement generated by {{ config('app.name', 'POS System') }}. Page 1 of 1
    </div>

</body>
</html>
