<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Audit Logs Report</title>
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

        /* Date Banner */
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

        /* Summary Stats Cards */
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
            background-color: #f0fdf4;
        }

        .summary-table td:nth-child(2) {
            background-color: #eff6ff;
        }

        .summary-table td:last-child {
            border-radius: 0 6px 6px 0;
            background-color: #fefce8;
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
            color: #0f172a;
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

        table.ledger td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: middle;
        }

        table.ledger tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: 'Courier New', monospace;
            font-size: 8.5px;
        }

        /* Type Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-auth { background-color: #e0e7ff; color: #3730a3; }
        .badge-sales { background-color: #dcfce7; color: #166534; }
        .badge-team { background-color: #dbeafe; color: #1e40af; }
        .badge-company { background-color: #f3e8ff; color: #6b21a8; }
        .badge-security { background-color: #fef3c7; color: #92400e; }
        .badge-default { background-color: #f1f5f9; color: #475569; }

        /* Footer */
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .actor-name {
            font-weight: bold;
            color: #0f172a;
        }
        .actor-email {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            color: #64748b;
        }
        .actor-role {
            font-size: 7.5px;
            color: #94a3b8;
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    {{-- Report Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: bottom;">
                <p class="company-title">{{ $companyName }}</p>
                <p class="report-title">Activity & Security Audit Trail Log</p>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <p class="meta-text">Report Generated: {{ now()->format('M d, Y \a\t g:i A') }}</p>
                <p class="meta-text">Total Recorded Entries: {{ count($logs) }}</p>
            </td>
        </tr>
    </table>

    {{-- Date Banner --}}
    <div class="date-banner">
        <strong>Period:</strong> {{ $dateRangeLabel }}
        @if($category && $category !== 'all')
            &nbsp;|&nbsp; <strong>Category:</strong> {{ strtoupper($category) }}
        @endif
    </div>

    {{-- Audit Logs Table --}}
    <table class="ledger">
        <thead>
            <tr>
                <th style="width: 15%;">Timestamp</th>
                <th style="width: 25%;">Actor & Credentials</th>
                <th style="width: 12%;">Category</th>
                <th style="width: 36%;">Activity Description</th>
                <th style="width: 12%;">IP Address</th>
            </tr>
        </thead>
        <tbody>
            @if(count($logs) > 0)
                @foreach($logs as $log)
                <tr>
                    <td class="font-mono">
                        {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}
                    </td>
                    <td>
                        <div class="actor-name">{{ $log->actor_name }}</div>
                        @if($log->actor_email || $log->user?->email || $log->employee?->email)
                            <div class="actor-email">{{ $log->actor_email ?: ($log->user?->email ?: $log->employee?->email) }}</div>
                        @endif
                        <div class="actor-role">{{ $log->actor_role }} &bull; {{ $log->actor_type }}</div>
                    </td>
                    <td>
                        @php
                            $badgeClass = 'badge-default';
                            if ($log->log_type === 'auth') $badgeClass = 'badge-auth';
                            elseif ($log->log_type === 'sales') $badgeClass = 'badge-sales';
                            elseif ($log->log_type === 'team') $badgeClass = 'badge-team';
                            elseif ($log->log_type === 'company') $badgeClass = 'badge-company';
                            elseif ($log->log_type === 'security') $badgeClass = 'badge-security';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $log->log_type }}</span>
                    </td>
                    <td>
                        <div><strong>{{ $log->description }}</strong></div>
                        @if($log->subject_title)
                            <div style="font-size: 8px; color: #4f46e5; margin-top: 1px;">Target: {{ $log->subject_title }}</div>
                        @endif
                    </td>
                    <td class="font-mono">
                        {{ $log->ip_address ?: '127.0.0.1' }}
                    </td>
                </tr>
                @endforeach
            @else
            <tr>
                <td colspan="5" class="text-center" style="padding: 30px; color: #94a3b8;">
                    No activity logs found matching the selected criteria.
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }} &bull; {{ $companyName }} Audit Log System</p>
        <p>This report contains {{ count($logs) }} activity record(s)</p>
    </div>
</body>
</html>
