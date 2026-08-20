<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Banking Transactions Ledger</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            margin: 0;
            padding: 15px;
            color: #1e293b;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
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

        /* Date Range Banner */
        .date-banner {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 14px;
            margin-bottom: 14px;
            font-size: 10px;
            color: #475569;
        }

        .date-banner strong {
            color: #0f172a;
        }

        /* Summary Cards */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .summary-table td {
            width: 33.33%;
            text-align: center;
            padding: 10px 8px;
            border: 1px solid #e2e8f0;
        }

        .summary-table td:first-child {
            border-radius: 6px 0 0 6px;
            background-color: #ecfdf5;
        }

        .summary-table td:nth-child(2) {
            background-color: #fef2f2;
        }

        .summary-table td:last-child {
            border-radius: 0 6px 6px 0;
            background-color: #f0f9ff;
        }

        .summary-label {
            display: block;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 3px;
        }

        .summary-value {
            display: block;
            font-size: 14px;
            font-weight: bold;
        }

        .summary-value.inflow {
            color: #059669;
        }

        .summary-value.outflow {
            color: #dc2626;
        }

        .summary-value.net-positive {
            color: #0284c7;
        }

        .summary-value.net-negative {
            color: #dc2626;
        }

        /* Main Table */
        table.ledger {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.ledger th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 6px;
            text-align: left;
            border: none;
        }

        table.ledger th:last-child,
        table.ledger th:nth-child(6),
        table.ledger th:nth-child(7) {
            text-align: right;
        }

        table.ledger td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: middle;
        }

        table.ledger tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.ledger tbody tr:hover {
            background-color: #f1f5f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: 'Courier New', monospace;
            font-size: 9px;
        }

        /* Type Badges */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-income {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-expense {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Amount Colors */
        .amount-positive {
            color: #059669;
            font-weight: bold;
        }

        .amount-negative {
            color: #dc2626;
            font-weight: bold;
        }

        .balance-value {
            font-weight: bold;
            color: #0f172a;
        }

        /* Footer */
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .footer p {
            margin: 2px 0;
        }

        /* Filters */
        .filters-box {
            background-color: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 8px 14px;
            margin-bottom: 14px;
            font-size: 9px;
            color: #78350f;
        }

        .filters-box strong {
            color: #451a03;
        }

        .filter-tag {
            display: inline-block;
            padding: 1px 6px;
            background-color: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 4px;
            margin-right: 6px;
            margin-bottom: 2px;
            font-size: 8.5px;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    {{-- Report Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: bottom;">
                <p class="company-title">{{ $companyName }}</p>
                <p class="report-title">Banking Transactions Ledger</p>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <p class="meta-text">Report Generated: {{ now()->format('M d, Y \a\t g:i A') }}</p>
                <p class="meta-text">Currency: {{ $currencyCode }}</p>
            </td>
        </tr>
    </table>

    {{-- Date Range Banner --}}
    <div class="date-banner">
        <strong>Period:</strong> {{ $dateRangeLabel }}
    </div>

    {{-- Active Filters --}}
    @if(!empty($activeFilters))
    <div class="filters-box">
        <strong>Active Filters:</strong>
        @foreach($activeFilters as $filterLabel)
            <span class="filter-tag">{{ $filterLabel }}</span>
        @endforeach
    </div>
    @endif

    {{-- Summary Section --}}
    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-label">Total Inflow</span>
                <span class="summary-value inflow">{{ $currencySymbol }} {{ number_format($totalInflow, 2) }}</span>
            </td>
            <td>
                <span class="summary-label">Total Outflow</span>
                <span class="summary-value outflow">{{ $currencySymbol }} {{ number_format($totalOutflow, 2) }}</span>
            </td>
            <td>
                <span class="summary-label">Net Balance</span>
                <span class="summary-value {{ $netBalance >= 0 ? 'net-positive' : 'net-negative' }}">
                    {{ $currencySymbol }} {{ number_format(abs($netBalance), 2) }}
                    @if($netBalance < 0) (Deficit) @endif
                </span>
            </td>
        </tr>
    </table>

    {{-- Transactions Table --}}
    <table class="ledger">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 16%;">Bank / Cash Account</th>
                <th style="width: 12%;">Reference</th>
                <th style="width: 22%;">Description</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 13%;">Amount</th>
                <th style="width: 15%;">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            @if(count($transactions) > 0)
                @foreach($transactions as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('M d, Y') }}</td>
                    <td>
                        {{ $tx->bankAccount->account_name ?? 'Cash in Hand' }}
                        @if($tx->bankAccount && $tx->bankAccount->bank_name)
                            <br><span style="font-size: 8px; color: #64748b;">{{ $tx->bankAccount->bank_name }}</span>
                        @endif
                    </td>
                    <td class="font-mono">{{ $tx->reference_number ?? '-' }}</td>
                    <td>{{ $tx->description ?? '-' }}</td>
                    <td class="text-center">
                        @if($tx->transaction_type === 'credit' || $tx->transaction_type === 'income')
                            <span class="badge badge-income">Income</span>
                        @else
                            <span class="badge badge-expense">Expense</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($tx->transaction_type === 'credit' || $tx->transaction_type === 'income')
                            <span class="amount-positive">+{{ $currencySymbol }} {{ number_format($tx->amount, 2) }}</span>
                        @else
                            <span class="amount-negative">-{{ $currencySymbol }} {{ number_format($tx->amount, 2) }}</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <span class="balance-value">{{ $currencySymbol }} {{ number_format($tx->running_balance, 2) }}</span>
                    </td>
                </tr>
                @endforeach
            @else
            <tr>
                <td colspan="7" class="text-center" style="padding: 30px; color: #94a3b8;">
                    No transactions found for the selected criteria.
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }} &bull; {{ $companyName }}</p>
        <p>This report contains {{ count($transactions) }} transaction(s)</p>
    </div>
</body>
</html>
