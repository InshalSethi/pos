<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bank Account Transactions Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }
        
        .filters {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        
        .filter-label {
            font-weight: bold;
            color: #555;
        }
        
        .summary {
            margin-bottom: 20px;
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .summary-value.positive {
            color: #059669;
        }
        
        .summary-value.negative {
            color: #dc2626;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            color: #555;
        }
        
        td {
            font-size: 11px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-debit {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge-credit {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .amount-debit {
            color: #dc2626;
        }
        
        .amount-credit {
            color: #059669;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bank Account Transactions Report</h1>
        <h2>{{ config('app.name', 'POS System') }}</h2>
    </div>

    @if($filters)
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(isset($filters['account_name']) && $filters['account_name'])
            <div class="filter-item">
                <span class="filter-label">Account:</span> {{ $filters['account_name'] }}
            </div>
        @endif
        @if((isset($filters['date_from']) && $filters['date_from']) || (isset($filters['date_to']) && $filters['date_to']))
            <div class="filter-item">
                <span class="filter-label">Date Range:</span>
                @if(isset($filters['date_from']) && isset($filters['date_to']) && $filters['date_from'] && $filters['date_to'])
                    {{ \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($filters['date_to'])->format('M d, Y') }}
                @elseif(isset($filters['date_from']) && $filters['date_from'])
                    From {{ \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') }}
                @elseif(isset($filters['date_to']) && $filters['date_to'])
                    Until {{ \Carbon\Carbon::parse($filters['date_to'])->format('M d, Y') }}
                @endif
            </div>
        @endif
        @if(isset($filters['transaction_type']) && $filters['transaction_type'])
            <div class="filter-item">
                <span class="filter-label">Type:</span> {{ ucfirst($filters['transaction_type']) }}
            </div>
        @endif
        @if(isset($filters['search']) && $filters['search'])
            <div class="filter-item">
                <span class="filter-label">Search:</span> "{{ $filters['search'] }}"
            </div>
        @endif
    </div>
    @endif

    @if($summary)
    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Transactions</div>
                <div class="summary-value">{{ number_format($summary['total_count']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Debits</div>
                <div class="summary-value negative">${{ number_format($summary['total_debits'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Credits</div>
                <div class="summary-value positive">${{ number_format($summary['total_credits'], 2) }}</div>
            </div>
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 15%;">Reference</th>
                <th style="width: 25%;">Description</th>
                <th style="width: 15%;">Account</th>
                <th style="width: 8%;">Type</th>
                <th style="width: 10%;" class="text-right">Debit</th>
                <th style="width: 10%;" class="text-right">Credit</th>
                <th style="width: 12%;" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
            <tr>
                <td>{{ $transaction['id'] }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction['entry_date'])->format('M d, Y') }}</td>
                <td>{{ $transaction['reference'] ?? '' }}</td>
                <td>{{ $transaction['description'] ?? '' }}</td>
                <td>{{ $transaction['account_name'] ?? '' }}</td>
                <td class="text-center">
                    @if($transaction['debit_amount'] > 0)
                        <span class="badge badge-debit">Debit</span>
                    @else
                        <span class="badge badge-credit">Credit</span>
                    @endif
                </td>
                <td class="text-right">
                    @if($transaction['debit_amount'] > 0)
                        <span class="amount-debit">${{ number_format($transaction['debit_amount'], 2) }}</span>
                    @endif
                </td>
                <td class="text-right">
                    @if($transaction['credit_amount'] > 0)
                        <span class="amount-credit">${{ number_format($transaction['credit_amount'], 2) }}</span>
                    @endif
                </td>
                <td class="text-right">
                    @if($transaction['running_balance'] >= 0)
                        <span class="amount-credit">${{ number_format($transaction['running_balance'], 2) }}</span>
                    @else
                        <span class="amount-debit">${{ number_format(abs($transaction['running_balance']), 2) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 40px; color: #666;">
                    No transactions found matching the selected criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }} | {{ config('app.name', 'POS System') }}</p>
        <p>This report contains {{ count($transactions) }} transaction(s)</p>
    </div>
</body>
</html>
