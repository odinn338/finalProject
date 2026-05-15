@extends('layouts.app')

@section('title', 'جدول الأقساط')
@section('page-title', 'جدول الأقساط')
@section('page-subtitle', 'دين رقم: ' . $debt->reference_number)

@section('content')
<div class="page-content">

    {{-- ملخص الدين --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3>
                <i class="fas fa-file-invoice-dollar" style="color:var(--primary);"></i>
                &nbsp;{{ $debt->reference_number }}
                <span class="badge badge-{{ $debt->status_color }}" style="margin-right:8px;">
                    {{ $debt->status_arabic }}
                </span>
            </h3>
            <a href="{{ route('debts.show', $debt->id) }}" class="btn-secondary btn-sm">
                <i class="fas fa-arrow-right"></i> تفاصيل الدين
            </a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
            <div>
                <div style="font-size:.72rem;color:var(--muted);">إجمالي الدين</div>
                <div style="font-weight:900;color:var(--primary);">
                    {{ number_format($debt->total_amount, 2) }} ج.م
                </div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);">المسدد</div>
                <div style="font-weight:900;color:var(--success);">
                    {{ number_format($debt->total_paid, 2) }} ج.م
                </div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);">المتبقي</div>
                <div style="font-weight:900;color:var(--danger);">
                    {{ number_format($debt->remaining_balance, 2) }} ج.م
                </div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);">نسبة الإنجاز</div>
                <div style="font-weight:900;color:var(--text);">
                    {{ $debt->progress_percentage }}%
                </div>
            </div>
        </div>

        <div style="margin-top:14px;">
            <div class="progress" style="height:8px;">
                <div class="progress-bar {{ $debt->progress_percentage >= 100 ? 'success' : '' }}"
                     style="width:{{ $debt->progress_percentage }}%;"></div>
            </div>
        </div>
    </div>

    {{-- جدول الأقساط --}}
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-table" style="color:var(--primary);"></i>
                &nbsp;جميع الأقساط ({{ $installments->count() }})
            </h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>تاريخ الاستحقاق</th>
                        <th>قيمة القسط</th>
                        <th>المسدد</th>
                        <th>غرامة التأخير</th>
                        <th>الحالة</th>
                        <th>تاريخ السداد</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($installments as $installment)
                        <tr style="
                            @if($installment->status === 'paid')    background:rgba(46,204,113,.04);
                            @elseif($installment->status === 'overdue') background:rgba(231,76,60,.04);
                            @elseif($installment->status === 'voided')  opacity:.45;
                            @endif
                        ">
                            <td style="font-weight:700;color:var(--primary);">
                                {{ $installment->installment_number }}
                            </td>
                            <td style="font-size:.88rem;">
                                {{ $installment->due_date->format('Y-m-d') }}
                                @if($installment->status === 'overdue')
                                    <div style="font-size:.7rem;color:var(--danger);">
                                        متأخر {{ $installment->days_overdue }} يوم
                                    </div>
                                @endif
                            </td>
                            <td style="font-weight:700;">
                                {{ number_format($installment->amount, 2) }} ج.م
                            </td>
                            <td style="color:var(--success);">
                                {{ number_format($installment->paid_amount, 2) }} ج.م
                            </td>
                            <td style="color:{{ $installment->penalty_amount > 0 ? 'var(--danger)' : 'var(--muted)' }};">
                                {{ number_format($installment->penalty_amount, 2) }} ج.م
                            </td>
                            <td>
                                <span class="badge badge-{{ $installment->status_color }}">
                                    {{ $installment->status_arabic }}
                                </span>
                            </td>
                            <td style="font-size:.82rem;color:var(--muted);">
                                {{ $installment->paid_date?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td>
                                @include('installments._actions', ['installment' => $installment])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
