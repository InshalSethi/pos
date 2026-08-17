<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee General Ledger - {{ $employee->full_name }}</title>
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

        .employee-info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .employee-info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .employee-info-table td {
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

        .stat-card.debits {
            border-left: 4px solid #ef4444;
            background-color: #fef2f2;
        }

        .stat-card.credits {
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

        .badge-debit { background-color: #fee2e2; color: #991b1b; }
        .badge-credit { background-color: #d1fae5; color: #065f46; }

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
                <div class="report-title">EMPLOYEE GENERAL LEDGER STATEMENT</div>
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

    <!-- Employee Basic Information -->
    <div class="employee-info-box">
        <table class="employee-info-table">
            <tr>
                <td style="width: 33%;">
                    <div class="info-label">Employee Name</div>
                    <div class="info-value">{{ $employee->full_name }}</div>
                </td>
                <td style="width: 33%;">
                    <div class="info-label">Department</div>
                    <div class="info-value">{{ $employee->department ? $employee->department->name : 'N/A' }}</div>
                </td>
                <td style="width: 34%;">
                    <div class="info-label">Position / Role</div>
                    <div class="info-value">{{ $employee->position ? $employee->position->name : 'N/A' }}</div>
                </td>
            </tr>
            <tr>
                <td style="margin-top: 5px;">
                    <div class="info-label">Employee ID</div>
                    <div class="info-value">{{ $employee->employee_number ?: '#' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</div>
                </td>
                <td style="margin-top: 5px;">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $employee->email ?: 'N/A' }}</div>
                </td>
                <td style="margin-top: 5px;">
                    <div class="info-label">Basic Salary</div>
                    <div class="info-value" style="color: #4f46e5;">${{ number_format((float)($employee->basic_salary ?? 0), 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Stats Cards -->
    <table class="stats-table">
        <tr>
            <td style="width: 32%; padding-right: 8px;">
                <div class="stat-card debits">
                    <div class="stat-label">Total Disbursements</div>
                    <div class="stat-amount" style="color: #dc2626;">${{ number_format($totalDebits, 2) }}</div>
                </div>
            </td>
            <td style="width: 32%; padding-right: 8px;">
                <div class="stat-card credits">
                    <div class="stat-label">Total Receipts</div>
                    <div class="stat-amount" style="color: #047857;">${{ number_format($totalCredits, 2) }}</div>
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

    <!-- Transactions Table -->
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
                        <span class="badge {{ $tx['debit'] > 0 ? 'badge-debit' : 'badge-credit' }}">
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
                        No transactions registered for this employee in selected period.
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
