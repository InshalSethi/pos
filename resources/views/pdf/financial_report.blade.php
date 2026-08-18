<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }} - {{ config('app.name', 'POS & ERP System') }}</title>
    <style>
        @page {
            margin: 25pt 30pt 25pt 30pt;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2.5px solid #4f46e5;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        .meta-info {
            font-size: 9px;
            color: #64748b;
            text-align: right;
            line-height: 1.4;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-indigo { background-color: #e0e7ff; color: #3730a3; }
        
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
        }
        .kpi-card.highlight {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }
        .kpi-card.indigo {
            background-color: #eeef4ff;
            border-color: #c7d2fe;
        }
        .kpi-label {
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }

        .section-header {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 15px;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #cbd5e1;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9.5px;
            color: #1e293b;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tr.subtotal-row td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 1px solid #94a3b8;
            border-bottom: 1px solid #94a3b8;
        }
        .data-table tr.total-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 10.5px;
            border-top: 2px solid #0f172a;
            border-bottom: 2px double #0f172a;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-mono { font-family: monospace; font-weight: bold; }
        .text-debit { color: #dc2626; font-weight: bold; }
        .text-credit { color: #16a34a; font-weight: bold; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 15px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="company-name">{{ $companyName ?? config('app.name', 'POS & ERP System') }}</div>
                <div class="report-title">{{ $title }}</div>
            </td>
            <td style="width: 40%;" class="meta-info">
                <div><strong>Period:</strong> {{ $periodText }}</div>
                <div><strong>Generated Date:</strong> {{ $generatedAt }}</div>
                <div style="margin-top: 2px;">
                    <span class="badge badge-indigo">IFRS / GAAP Compliant</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- REPORT BODY: PROFIT & LOSS -->
    @if($reportType === 'profit-loss')
        <table class="kpi-table">
            <tr>
                <td style="width: 32%; padding-right: 6px;">
                    <div class="kpi-card">
                        <div class="kpi-label">Net Sales Revenue</div>
                        <div class="kpi-value">${{ number_format($data['net_revenue'] ?? 0, 2) }}</div>
                    </div>
                </td>
                <td style="width: 32%; padding-right: 6px;">
                    <div class="kpi-card highlight">
                        <div class="kpi-label">Gross Profit ({{ $data['gross_profit_margin'] ?? 0 }}%)</div>
                        <div class="kpi-value">${{ number_format($data['gross_profit'] ?? 0, 2) }}</div>
                    </div>
                </td>
                <td style="width: 36%;">
                    <div class="kpi-card highlight">
                        <div class="kpi-label">Net Income ({{ $data['net_income_margin'] ?? 0 }}%)</div>
                        <div class="kpi-value">${{ number_format($data['net_income'] ?? 0, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-header">I. Operating Revenue</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Account Code</th>
                    <th style="width: 60%;">Account Description</th>
                    <th style="width: 25%;" class="text-right">Amount ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['gross_revenue'] ?? [] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['account_code'] }}</td>
                        <td>{{ $row['account_name'] }}</td>
                        <td class="text-right">${{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="2">Total Gross Sales Revenue</td>
                    <td class="text-right">${{ number_format($data['total_gross_revenue'] ?? 0, 2) }}</td>
                </tr>
                @foreach($data['discounts_returns'] ?? [] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['account_code'] }}</td>
                        <td>Less: {{ $row['account_name'] }}</td>
                        <td class="text-right text-debit">(${{ number_format($row['amount'], 2) }})</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2">Net Sales Revenue</td>
                    <td class="text-right">${{ number_format($data['net_revenue'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-header">II. Cost of Goods Sold (COGS)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Account Code</th>
                    <th style="width: 60%;">Account Description</th>
                    <th style="width: 25%;" class="text-right">Amount ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['cogs'] ?? [] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['account_code'] }}</td>
                        <td>{{ $row['account_name'] }}</td>
                        <td class="text-right">${{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2">Gross Profit (Margin: {{ $data['gross_profit_margin'] ?? 0 }}%)</td>
                    <td class="text-right">${{ number_format($data['gross_profit'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-header">III. Operating Expenses (OpEx)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Account Code</th>
                    <th style="width: 60%;">Account Description</th>
                    <th style="width: 25%;" class="text-right">Amount ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['operating_expenses'] ?? [] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['account_code'] }}</td>
                        <td>{{ $row['account_name'] }}</td>
                        <td class="text-right">${{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="2">Total Operating Expenses</td>
                    <td class="text-right">${{ number_format($data['total_operating_expenses'] ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2">Operating Profit / EBITDA (Margin: {{ $data['operating_profit_margin'] ?? 0 }}%)</td>
                    <td class="text-right">${{ number_format($data['operating_profit'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-header">IV. Non-Operating & Net Income</div>
        <table class="data-table">
            <tbody>
                <tr class="total-row">
                    <td style="width: 75%;">Net Income / Profit For The Period</td>
                    <td style="width: 25%;" class="text-right">${{ number_format($data['net_income'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

    <!-- REPORT BODY: BALANCE SHEET -->
    @elseif($reportType === 'balance-sheet')
        <table class="kpi-table">
            <tr>
                <td style="width: 32%; padding-right: 6px;">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Assets</div>
                        <div class="kpi-value">${{ number_format($data['total_assets'] ?? 0, 2) }}</div>
                    </div>
                </td>
                <td style="width: 32%; padding-right: 6px;">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Liabilities</div>
                        <div class="kpi-value">${{ number_format($data['total_liabilities'] ?? 0, 2) }}</div>
                    </div>
                </td>
                <td style="width: 36%;">
                    <div class="kpi-card highlight">
                        <div class="kpi-label">Total Equity</div>
                        <div class="kpi-value">${{ number_format($data['total_equity'] ?? 0, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-header">ASSETS</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Account Code</th>
                    <th style="width: 60%;">Account Description</th>
                    <th style="width: 25%;" class="text-right">Balance ($)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="subtotal-row"><td colspan="3">Current Assets</td></tr>
                @foreach($data['current_assets'] ?? [] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['account_code'] }}</td>
                        <td>{{ $row['account_name'] }}</td>
                        <td class="text-right">${{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="2">Total Current Assets</td>
                    <td class="text-right">${{ number_format($data['total_current_assets'] ?? 0, 2) }}</td>
                </tr>

                <tr class="subtotal-row"><td colspan="3">Fixed & Non-Current Assets</td></tr>
                @foreach($data['fixed_assets'] ?? [] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['account_code'] }}</td>
                        <td>{{ $row['account_name'] }}</td>
                        <td class="text-right">${{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2">TOTAL ASSETS</td>
                    <td class="text-right">${{ number_format($data['total_assets'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-header">LIABILITIES & OWNER EQUITY</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Account Code</th>
                    <th style="width: 60%;">Account Description</th>
                    <th style="width: 25%;" class="text-right">Balance ($)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="subtotal-row"><td colspan="3">Current Liabilities</td></tr>
                @foreach($data['current_liabilities'] ?? [] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['account_code'] }}</td>
                        <td>{{ $row['account_name'] }}</td>
                        <td class="text-right">${{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="2">Total Liabilities</td>
                    <td class="text-right">${{ number_format($data['total_liabilities'] ?? 0, 2) }}</td>
                </tr>

                <tr class="subtotal-row"><td colspan="3">Owner Equity & Retained Earnings</td></tr>
                @foreach($data['equity_accounts'] ?? [] as $row)
                    <tr>
                        <td class="font-mono">{{ $row['account_code'] }}</td>
                        <td>{{ $row['account_name'] }}</td>
                        <td class="text-right">${{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="font-mono">3999</td>
                    <td>Current Period Retained Net Income</td>
                    <td class="text-right">${{ number_format($data['current_period_net_income'] ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2">TOTAL LIABILITIES & EQUITY</td>
                    <td class="text-right">${{ number_format($data['total_liabilities_equity'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

    <!-- REPORT BODY: TRIAL BALANCE -->
    @elseif($reportType === 'trial-balance')
        <div class="section-header">General Ledger Trial Balance</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Code</th>
                    <th style="width: 48%;">Account Name</th>
                    <th style="width: 15%;">Type</th>
                    <th style="width: 12.5%;" class="text-right">Debit ($)</th>
                    <th style="width: 12.5%;" class="text-right">Credit ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['accounts'] ?? [] as $acc)
                    <tr>
                        <td class="font-mono">{{ $acc['account_code'] }}</td>
                        <td>{{ $acc['account_name'] }}</td>
                        <td style="text-transform: uppercase; font-size: 8px; color: #64748b;">{{ $acc['account_type'] }}</td>
                        <td class="text-right font-mono">{{ $acc['debit_balance'] > 0 ? '$' . number_format($acc['debit_balance'], 2) : '-' }}</td>
                        <td class="text-right font-mono">{{ $acc['credit_balance'] > 0 ? '$' . number_format($acc['credit_balance'], 2) : '-' }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3">TOTAL GENERAL LEDGER AUDIT BALANCE</td>
                    <td class="text-right font-mono">${{ number_format($data['total_debits'] ?? 0, 2) }}</td>
                    <td class="text-right font-mono">${{ number_format($data['total_credits'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

    <!-- REPORT BODY: CASH FLOW STATEMENT -->
    @elseif($reportType === 'cash-flow')
        <div class="section-header">I. Cash Flow from Operating Activities</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 75%;">Operating Description</th>
                    <th style="width: 25%;" class="text-right">Amount ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['operating_activities'] ?? [] as $act)
                    <tr>
                        <td>{{ $act['description'] }}</td>
                        <td class="text-right">${{ number_format($act['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td>Net Cash Provided by / (Used in) Operating Activities</td>
                    <td class="text-right">${{ number_format($data['net_operating_cash'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-header">II. Cash Flow from Investing & Financing Activities</div>
        <table class="data-table">
            <tbody>
                <tr class="subtotal-row">
                    <td style="width: 75%;">Net Cash Provided by Investing Activities</td>
                    <td style="width: 25%;" class="text-right">${{ number_format($data['net_investing_cash'] ?? 0, 2) }}</td>
                </tr>
                <tr class="subtotal-row">
                    <td>Net Cash Provided by Financing Activities</td>
                    <td class="text-right">${{ number_format($data['net_financing_cash'] ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>NET INCREASE / (DECREASE) IN CASH & CASH EQUIVALENTS</td>
                    <td class="text-right">${{ number_format($data['net_change_in_cash'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Beginning Cash Balance</td>
                    <td class="text-right">${{ number_format($data['beginning_cash'] ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>ENDING CASH & CASH EQUIVALENTS BALANCE</td>
                    <td class="text-right">${{ number_format($data['ending_cash'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

    <!-- GENERIC / OPERATIONAL REPORTS -->
    @else
        <div class="section-header">Report Summary & Detail Breakdown</div>
        <table class="data-table">
            <thead>
                <tr>
                    @if(isset($data['daily_breakdown']) || isset($data['summary']))
                        <th>Date / Metric</th>
                        <th class="text-right">Total Sales</th>
                        <th class="text-right">Revenue</th>
                        <th class="text-right">Paid</th>
                    @elseif(isset($data['products']))
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th class="text-right">Quantity Sold</th>
                        <th class="text-right">Total Revenue</th>
                    @else
                        <th>Description</th>
                        <th class="text-right">Value</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if(isset($data['daily_breakdown']))
                    @foreach($data['daily_breakdown'] as $row)
                        <tr>
                            <td>{{ $row['date'] ?? $row['month_name'] ?? 'N/A' }}</td>
                            <td class="text-right">{{ $row['total_sales'] ?? 0 }}</td>
                            <td class="text-right">${{ number_format($row['total_revenue'] ?? 0, 2) }}</td>
                            <td class="text-right">${{ number_format($row['total_paid'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                @elseif(is_array($data))
                    @foreach($data as $key => $val)
                        @if(is_numeric($val) || is_string($val))
                            <tr>
                                <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}</td>
                                <td class="text-right">{{ is_numeric($val) ? '$' . number_format($val, 2) : $val }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            </tbody>
        </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        Official Financial Statement generated by {{ config('app.name', 'POS System') }} on {{ $generatedAt }}. Page 1 of 1
    </div>

</body>
</html>
