@extends('layouts.app')

@section('title', 'تأكيد مدفوعات الأقساط')
@section('page-title', 'أقساط بانتظار التأكيد')
@section('page-subtitle', 'راجع طلبات السداد المقدَّمة من المدينين ثم أكّد التحويل بين المحافظ')

@section('content')
<div class="page-content" style="padding:20px 32px;">

    @if($installments->isEmpty())
        <div class="card" style="text-align:center;padding:48px 24px;">
            <i class="fas fa-check-circle" style="font-size:3rem;color:var(--success);opacity:0.7;display:block;margin-bottom:12px;"></i>
            <h3 style="color:var(--muted);">لا توجد أقساط بانتظار التأكيد</h3>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-clock" style="color:var(--warning);"></i> طلبات السداد المعلّقة ({{ $installments->total() }})</h3>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>القسط</th>
                            <th>الدين</th>
                            <th>المدين</th>
                            <th>الدائن</th>
                            <th>المبلغ</th>
                            <th>مرجع الدفع</th>
                            <th>ملاحظات</th>
                            <th>تاريخ الطلب</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($installments as $installment)
                            <tr>
                                <td style="font-weight:700;">#{{ $installment->installment_number }}</td>
                                <td>{{ $installment->debt?->reference_number ?? '—' }}</td>
                                <td>{{ $installment->debt?->borrower?->name ?? $installment->user?->name ?? '—' }}</td>
                                <td>{{ $installment->debt?->lender?->name ?? '—' }}</td>
                                <td style="font-weight:700;">{{ number_format($installment->remaining_amount, 2) }} ج.م</td>
                                <td>{{ $installment->payment_reference ?? '—' }}</td>
                                <td style="max-width:180px;font-size:0.82rem;">{{ \Illuminate\Support\Str::limit($installment->notes ?? '', 60) }}</td>
                                <td style="font-size:0.82rem;">{{ $installment->updated_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if(auth()->user()->isAdmin() && $installment->status === \App\Models\Installment::STATUS_PENDING_APPROVAL)
                                        <form method="POST"
                                              action="{{ route('admin.installments.approve', ['installment' => $installment->id]) }}"
                                              onsubmit="return confirm('تأكيد الدفع وتحويل {{ number_format($installment->remaining_amount, 2) }} ج.م من محفظة المدين إلى الدائن؟')"
                                              style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-success btn-sm">
                                                <i class="fas fa-check-circle"></i> تأكيد الدفع
                                            </button>
                                        </form>
                                        <a href="{{ route('debts.show', $installment->debt_id) }}" class="btn-secondary btn-sm" style="margin-right:6px;">
                                            عرض الدين
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px;">{{ $installments->links() }}</div>
        </div>
    @endif
</div>
@endsection
