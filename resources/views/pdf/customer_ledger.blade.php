<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Ledger Statement - {{ $customer->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            margin: 0;
            padding: 15px;
            color: #1e293b;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
        }

        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #475569;
            margin: 3px 0 0 0;
        }

        .meta-text {
            font-size: 9px;
            color: #64748b;
        }

        /* Executive Status Alert Box */
        .executive-banner {
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .banner-due {
            background-color: #fff1f2;
            border: 1.5px solid #f43f5e;
            color: #9f1239;
        }
        .banner-advance {
            background-color: #ecfdf5;
            border: 1.5px solid #10b981;
            color: #065f46;
        }
        .banner-cleared {
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            color: #334155;
        }

        .customer-info-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }

        .customer-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customer-info-table td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 9px;
        }

        .info-label {
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            font-size: 8px;
        }

        .info-value {
            color: #0f172a;
            font-weight: bold;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .stat-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }

        .stat-card.opening { border-left: 3px solid #64748b; background-color: #f8fafc; }
        .stat-card.debits { border-left: 3px solid #e11d48; background-color: #fff1f2; }
        .stat-card.credits { border-left: 3px solid #059669; background-color: #ecfdf5; }
        .stat-card.balance { border-left: 3px solid #be123c; background-color: #ffe4e6; }

        .stat-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 2px;
        }

        .stat-amount {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }

        /* GENERAL LEDGER TABLE STYLES (RED FOR DEBIT, GREEN FOR CREDIT) */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .ledger-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #334155;
            text-align: left;
        }

        .ledger-table th.group-header {
            background-color: #1e293b;
            text-align: center;
        }

        .ledger-table th.sub-header-debit {
            background-color: #881337;
            color: #fecdd3;
            font-size: 8px;
            text-align: right;
        }

        .ledger-table th.sub-header-credit {
            background-color: #064e3b;
            color: #a7f3d0;
            font-size: 8px;
            text-align: right;
        }

        .ledger-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            color: #1e293b;
            vertical-align: top;
        }

        .ledger-table tr.opening-row {
            background-color: #f8fafc;
            font-weight: bold;
        }

        .ledger-table tr.summary-row {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
        }

        .ledger-table tr.summary-row td {
            border: 1px solid #334155;
            color: #ffffff;
            font-size: 9.5px;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* RED DEBIT, GREEN CREDIT */
        .text-debit { color: #e11d48 !important; font-weight: bold; }
        .text-credit { color: #059669 !important; font-weight: bold; }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
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
                <div style="font-size: 9px; font-weight: bold; color: #4f46e5; margin-top: 2px;">
                    ACCOUNT CODE: {{ $accountCode ?? ('AR-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT)) }} (Accounts Receivable)
                </div>
            </td>
            <td class="text-right" style="vertical-align: bottom;">
                <div class="meta-text"><strong>Statement Period:</strong> 
                    @if($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    @elseif($startDate)
                        From {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                    @elseif($endDate)
                        Until {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    @else
                        All Time Chronological
                    @endif
                </div>
                <div class="meta-text"><strong>Issue Date & Time:</strong> {{ now()->format('M d, Y g:i A') }}</div>
            </td>
        </tr>
    </table>

    <!-- EXECUTIVE FINANCIAL SUMMARY BANNER -->
    @if($closingBalance > 0)
        <div class="executive-banner banner-due">
            <strong>ACTION REQUIRED — OUTSTANDING PAYMENT DUE:</strong><br>
            You have to receive <strong>${{ number_format($closingBalance, 2) }}</strong> in outstanding balance from <strong>{{ $customer->name }}</strong>.
        </div>
    @elseif($closingBalance < 0)
        <div class="executive-banner banner-advance">
            <strong>CUSTOMER ADVANCE WALLET CREDIT:</strong><br>
            This customer has overpaid / holds <strong>${{ number_format(abs($closingBalance), 2) }}</strong> in wallet credit balance. No payment is currently due.
        </div>
    @else
        <div class="executive-banner banner-cleared">
            <strong>ACCOUNT FULLY SETTLED:</strong><br>
            All sales invoices for <strong>{{ $customer->name }}</strong> have been paid in full ($0.00 Outstanding).
        </div>
    @endif

    <!-- Customer Basic Information -->
    <div class="customer-info-box">
        <table class="customer-info-table">
            <tr>
                <td style="width: 25%;">
                    <div class="info-label">Customer ID / Code</div>
                    <div class="info-value">{{ $accountCode ?? ('# ' . str_pad($customer->id, 4, '0', STR_PAD_LEFT)) }}</div>
                </td>
                <td style="width: 35%;">
                    <div class="info-label">Customer Legal Name</div>
                    <div class="info-value">{{ $customer->name }}</div>
                </td>
                <td style="width: 20%;">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">{{ $customer->phone ?: 'N/A' }}</div>
                </td>
                <td style="width: 20%;">
                    <div class="info-label">Credit Limit</div>
                    <div class="info-value">${{ number_format((float)($customer->credit_limit ?? 0), 2) }}</div>
                </td>
            </tr>
            <tr>
                <td style="margin-top: 4px;">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $customer->email ?: 'N/A' }}</div>
                </td>
                <td style="margin-top: 4px;" colspan="2">
                    <div class="info-label">Billing Address</div>
                    <div class="info-value">{{ implode(', ', array_filter([$customer->address, $customer->city, $customer->state, $customer->country])) ?: 'N/A' }}</div>
                </td>
                <td style="margin-top: 4px;">
                    <div class="info-label">Wallet Advance</div>
                    <div class="info-value" style="color: #059669;">${{ number_format((float)($customer->wallet_balance ?? 0), 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Stats Cards -->
    <table class="stats-table">
        <tr>
            <td style="width: 25%; padding-right: 6px;">
                <div class="stat-card opening">
                    <div class="stat-label">Opening Balance B/F</div>
                    <div class="stat-amount" style="color: #475569;">${{ number_format($openingBalance, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 6px;">
                <div class="stat-card debits">
                    <div class="stat-label">Total Billed (Debit)</div>
                    <div class="stat-amount" style="color: #e11d48;">${{ number_format($totalDebits, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 6px;">
                <div class="stat-card credits">
                    <div class="stat-label">Total Received (Credit)</div>
                    <div class="stat-amount" style="color: #059669;">${{ number_format($totalCredits, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="stat-card balance">
                    <div class="stat-label">Net Due To Receive</div>
                    <div class="stat-amount" style="color: {{ $closingBalance > 0 ? '#e11d48' : '#059669' }};">
                        ${{ number_format(abs($closingBalance), 2) }} {{ $closingBalanceType ?? 'Dr' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- GENERAL LEDGER TABLE (RED DEBIT, GREEN CREDIT) -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 12%;">Date</th>
                <th rowspan="2" style="width: 34%;">Description / Details</th>
                <th rowspan="2" style="width: 16%;">Journal Ref</th>
                <th colspan="2" class="group-header" style="width: 19%;">Transactions</th>
                <th colspan="2" class="group-header" style="width: 19%;">Running Balance</th>
            </tr>
            <tr>
                <th class="sub-header-debit">Debit (Sales +)</th>
                <th class="sub-header-credit">Credit (Paid -)</th>
                <th class="sub-header-debit">Debit (Dr)</th>
                <th class="sub-header-credit">Credit (Cr)</th>
            </tr>
        </thead>
        <tbody>
            @if($startDate)
                <tr class="opening-row">
                    <td>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</td>
                    <td><strong>Balance B/F (Opening Balance)</strong></td>
                    <td style="font-weight: bold; color: #475569;">OPENING</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right text-debit">{{ $openingBalance > 0 ? '$' . number_format($openingBalance, 2) . ' Dr' : '-' }}</td>
                    <td class="text-right text-credit">{{ $openingBalance < 0 ? '$' . number_format(abs($openingBalance), 2) . ' Cr' : '-' }}</td>
                </tr>
            @endif

            @forelse($transactions as $tx)
                <tr>
                    <td>{{ $tx['date'] ? \Carbon\Carbon::parse($tx['date'])->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $tx['particulars'] ?: $tx['description'] }}</div>
                        <div style="font-size: 8px; color: #64748b;">
                            Status: <strong>{{ $tx['status'] ?? 'Posted' }}</strong>
                            @if(!empty($tx['due_amount']) && $tx['due_amount'] > 0)
                                | <span style="color: #e11d48; font-weight: bold;">Due: ${{ number_format($tx['due_amount'], 2) }}</span>
                            @endif
                        </div>
                        @if(!empty($tx['items']))
                            <div style="margin-top: 3px; font-size: 8px; color: #475569;">
                                @foreach($tx['items'] as $item)
                                    <div>• {{ $item['name'] }} ({{ $item['qty'] }} x ${{ number_format($item['price'], 2) }} = ${{ number_format($item['total'], 2) }})</div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td style="font-weight: bold; color: #4f46e5;">{{ $tx['reference'] }}</td>
                    
                    <!-- Transactions Debit (RED) -->
                    <td class="text-right">
                        @if($tx['debit'] > 0)
                            <span class="text-debit">${{ number_format($tx['debit'], 2) }}</span>
                        @else
                            -
                        @endif
                    </td>

                    <!-- Transactions Credit (GREEN) -->
                    <td class="text-right">
                        @if($tx['credit'] > 0)
                            <span class="text-credit">${{ number_format($tx['credit'], 2) }}</span>
                        @else
                            -
                        @endif
                    </td>

                    <!-- Balance Debit (Dr RED) -->
                    <td class="text-right font-bold text-debit">
                        @if($tx['balance'] > 0)
                            <span>${{ number_format($tx['balance'], 2) }} <span style="font-size: 8px;">Dr</span></span>
                        @else
                            -
                        @endif
                    </td>

                    <!-- Balance Credit (Cr GREEN) -->
                    <td class="text-right font-bold text-credit">
                        @if($tx['balance'] < 0)
                            <span>${{ number_format(abs($tx['balance']), 2) }} <span style="font-size: 8px;">Cr</span></span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #94a3b8; font-style: italic;">
                        No ledger transactions found for this customer in the selected period.
                    </td>
                </tr>
            @endforelse

            <tr class="summary-row">
                <td colspan="3" class="text-center uppercase" style="letter-spacing: 0.5px;">Total Summary</td>
                <td class="text-right" style="color: #fecdd3;">${{ number_format($totalDebits, 2) }}</td>
                <td class="text-right" style="color: #a7f3d0;">${{ number_format($totalCredits, 2) }}</td>
                <td class="text-right" style="color: #fecdd3;">{{ $closingBalance > 0 ? '$' . number_format($closingBalance, 2) . ' Dr' : '-' }}</td>
                <td class="text-right" style="color: #a7f3d0;">{{ $closingBalance < 0 ? '$' . number_format(abs($closingBalance), 2) . ' Cr' : '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        IAS 1 / IFRS Compliant General Ledger Statement — Generated by {{ config('app.name', 'POS System') }}
    </div>

</body>
</html>
