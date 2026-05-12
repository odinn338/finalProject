@extends('layouts.app')

@section('title', 'ديوني')
@section('page-title', 'قائمة الديون')
@section('page-subtitle', 'جميع الديون المسجلة باسمك')

@section('content')
<div class="page-content">

    @if($debts->isEmpty())
        <div style="text-align:center;padding:60px 20px;">
            <i class="fas fa-inbox" style="font-size:4rem;color:var(--muted);opacity:0.3;margin-bottom:16px;display:block;"></i>
            <h3 style="color:var(--muted);margin-bottom:8px;">لا توجد ديون مسجلة</h3>
            <p style="color:var(--muted);font-size:0.88rem;margin-bottom:20px;">لم تتم الموافقة على أي طلب دين بعد.</p>
            <a href="{{ route('debt-requests.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i> تقديم طلب دين جديد
            </a>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar" style="color:var(--primary);"></i> &nbsp;الديون</h3>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>رقم الدين</th>
                            @if(auth()->user()->isAdmin())<th>المستخدم</th>@endif
                            <th>المبلغ الأصلي</th>
                            <th>الفائدة</th>
                            <th>الإجمالي</th>
                            <th>المسدد</th>
                            <th>المتبقي</th>
                            <th>القسط الشهري</th>
                            <th>الإنجاز</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($debts as $debt)
                            <tr>
                                <td>
                                    <span style="font-weight:700;color:var(--primary);">{{ $debt->reference_number }}</span>
                                    <div style="font-size:0.72rem;color:var(--muted);">{{ $debt->start_date->format('Y-m-d') }}</div>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td>{{ $debt->user->name }}</td>
                                @endif
                                <td>{{ number_format($debt->principal_amount, 2) }} ج.م</td>
                                <td style="color:var(--warning);">{{ $debt->interest_rate }}%</td>
                                <td style="font-weight:700;">{{ number_format($debt->total_amount, 2) }} ج.م</td>
                                <td style="color:var(--success);font-weight:700;">{{ number_format($debt->total_paid, 2) }} ج.م</td>
                                <td style="color:var(--danger);font-weight:700;">{{ number_format($debt->remaining_balance, 2) }} ج.م</td>
                                <td>{{ number_format($debt->monthly_installment, 2) }} ج.م</td>
                                <td style="min-width:100px;">
                                    <div class="progress" style="height:6px;margin-bottom:4px;">
                                        <div class="progress-bar {{ $debt->progress_percentage >= 100 ? 'success' : '' }}"
                                             style="width:{{ $debt->progress_percentage }}%;"></div>
                                    </div>
                                    <span style="font-size:0.72rem;color:var(--muted);">{{ $debt->progress_percentage }}%</span>
                                </td>
                                <td><span class="badge badge-{{ $debt->status_color }}">{{ $debt->status_arabic }}</span></td>
                                <td>
                                    <a href="{{ route('debts.show', $debt->id) }}" class="btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $debts->links() }}</div>
        </div>
    @endif

</div>
@endsection
