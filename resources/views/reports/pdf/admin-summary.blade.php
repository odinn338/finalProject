<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملخص تنفيذي — المدير</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; direction: rtl; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: right; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>ملخص تنفيذي</h1>
    <p>تاريخ التوليد: {{ $data['generated_at'] }}</p>
    <ul>
        <li>إجمالي المستخدمين: {{ $data['total_users'] }}</li>
        <li>إجمالي المحفظة (المبالغ): {{ number_format($data['total_portfolio'], 2) }} ج.م</li>
        <li>إجمالي المحصّل: {{ number_format($data['total_collected'], 2) }} ج.م</li>
        <li>ديون نشطة: {{ $data['active_debts'] }}</li>
        <li>ديون متأخرة: {{ $data['overdue_debts'] }}</li>
    </ul>
    <h2>أعلى المدينين بالمتبقي</h2>
    <table>
        <thead>
            <tr>
                <th>المدين</th>
                <th>رقم الدين</th>
                <th>المتبقي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['top_debtors'] as $d)
                <tr>
                    <td>{{ $d->borrower?->name ?? $d->user?->name ?? '—' }}</td>
                    <td>{{ $d->reference_number }}</td>
                    <td>{{ number_format($d->remaining_balance, 2) }} ج.م</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
