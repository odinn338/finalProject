<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملخص الديون - {{ $user->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a2e; direction: rtl; }
        .header { background: #1a1a2e; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 18px; }
        .header .date { font-size: 11px; opacity: 0.7; }
        .user-info { background: #f8f9fa; padding: 14px 20px; border-bottom: 2px solid #6c63ff; }
        .user-info h2 { font-size: 14px; color: #1a1a2e; margin-bottom: 4px; }
        .stats { display: flex; gap: 0; border-bottom: 1px solid #e0e0e0; }
        .stat-box { flex: 1; padding: 14px; text-align: center; border-left: 1px solid #e0e0e0; }
        .stat-box:last-child { border-left: none; }
        .stat-box .label { font-size: 10px; color: #666; margin-bottom: 4px; }
        .stat-box .value { font-size: 14px; font-weight: bold; }
        .stat-box.total .value  { color: #6c63ff; }
        .stat-box.paid .value   { color: #2ecc71; }
        .stat-box.remain .value { color: #e74c3c; }
        .section-title { background: #6c63ff; color: white; padding: 8px 16px; font-size: 12px; font-weight: bold; margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f0f0f8; padding: 8px 10px; font-size: 10px; color: #555; text-align: right; border: 1px solid #ddd; }
        td { padding: 7px 10px; font-size: 10px; border: 1px solid #eee; }
        tr:nth-child(even) td { background: #fafafa; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger  { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-primary { background: #cce5ff; color: #004085; }
        .footer { text-align: center; font-size: 9px; color: #999; padding: 16px; border-top: 1px solid #e0e0e0; margin-top: 20px; }
        .page-break { page-break-after: always; }
        .summary-row { font-weight: bold; background: #f0f0f8 !important; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1>🪙 Debt Mate - ملخص الديون</h1>
        <div class="date">تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</div>
    </div>
    <div style="text-align:left;font-size:11px;opacity:0.8;">
        نظام إدارة الديون الذكي
    </div>
</div>

<div class="user-info">
    <h2>{{ $user->name }}</h2>
    <div style="font-size:11px;color:#555;">
        البريد: {{ $user->email }} &nbsp;|&nbsp;
        الهاتف: {{ $user->phone ?? '-' }} &nbsp;|&nbsp;
        درجة الائتمان: {{ $user->credit_score }}/100
    </div>
</div>

<div class="stats">
    <div class="stat-box total">
        <div class="label">إجمالي الديون</div>
        <div class="value">{{ number_format($totalDebt, 2) }} ج.م</div>
    </div>
    <div class="stat-box paid">
        <div class="label">المسدد</div>
        <div class="value">{{ number_format($totalPaid, 2) }} ج.م</div>
    </div>
    <div class="stat-box remain">
        <div class="label">المتبقي</div>
        <div class="value">{{ number_format($totalRemaining, 2) }} ج.م</div>
    </div>
    <div class="stat-box">
        <div class="label">عدد الديون</div>
        <div class="value">{{ $debts->count() }}</div>
    </div>
</div>

@foreach($debts as $debt)
<div class="section-title">
    دين رقم: {{ $debt->reference_number }}
    &nbsp;|&nbsp; الحالة: {{ $debt->status_arabic }}
    &nbsp;|&nbsp; {{ $debt->start_date->format('Y-m-d') }} ← {{ $debt->end_date->format('Y-m-d') }}
</div>

<table>
    <tr>
        <th>المبلغ الأصلي</th>
        <th>نسبة الفائدة</th>
        <th>قيمة الفائدة</th>
        <th>الإجمالي</th>
        <th>القسط الشهري</th>
        <th>المسدد</th>
        <th>المتبقي</th>
        <th>الإنجاز</th>
    </tr>
    <tr>
        <td>{{ number_format($debt->principal_amount, 2) }}</td>
        <td>{{ $debt->interest_rate }}%</td>
        <td>{{ number_format($debt->interest_amount, 2) }}</td>
        <td style="font-weight:bold;">{{ number_format($debt->total_amount, 2) }}</td>
        <td>{{ number_format($debt->monthly_installment, 2) }}</td>
        <td style="color:#2ecc71;font-weight:bold;">{{ number_format($debt->total_paid, 2) }}</td>
        <td style="color:#e74c3c;font-weight:bold;">{{ number_format($debt->remaining_balance, 2) }}</td>
        <td>{{ $debt->progress_percentage }}%</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>تاريخ الاستحقاق</th>
            <th>قيمة القسط</th>
            <th>المسدد</th>
            <th>غرامة</th>
            <th>الحالة</th>
            <th>تاريخ السداد</th>
        </tr>
    </thead>
    <tbody>
        @foreach($debt->installments as $inst)
        <tr @if($inst->status === 'paid') style="background:#f0fdf4;" @elseif($inst->status === 'overdue') style="background:#fff5f5;" @endif>
            <td>{{ $inst->installment_number }}</td>
            <td>{{ $inst->due_date->format('Y-m-d') }}</td>
            <td>{{ number_format($inst->amount, 2) }}</td>
            <td>{{ number_format($inst->paid_amount, 2) }}</td>
            <td>{{ number_format($inst->penalty_amount, 2) }}</td>
            <td>
                <span class="badge badge-{{ $inst->status_color }}">{{ $inst->status_arabic }}</span>
            </td>
            <td>{{ $inst->paid_date ? $inst->paid_date->format('Y-m-d') : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(!$loop->last)
<div class="page-break"></div>
@endif
@endforeach

<div class="footer">
    تم إنشاء هذا التقرير بواسطة نظام Debt Mate &nbsp;|&nbsp; {{ now()->format('Y-m-d H:i:s') }}<br>
    هذا التقرير سري ومخصص لـ {{ $user->name }} فقط
</div>

</body>
</html>
