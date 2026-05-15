{{-- $installment must be passed into this partial --}}

@php
    $isPendingApproval = $installment->status === \App\Models\Installment::STATUS_PENDING_APPROVAL;
    $canPay = in_array($installment->status, [
        \App\Models\Installment::STATUS_PENDING,
        \App\Models\Installment::STATUS_OVERDUE,
    ], true);
    $debtorId = (int) ($installment->debt->debtor_id ?? $installment->debt->user_id);
@endphp

<span class="badge badge-{{ $installment->status_color }}">
    {{ $installment->status_arabic }}
</span>

@if ($canPay && auth()->user()->isDebtor() && $debtorId === (int) auth()->id())
    <a href="{{ route('installments.pay', ['installment' => $installment->id]) }}" class="btn-success btn-sm" style="margin-right:6px;">
        <i class="fas fa-money-bill-wave"></i> طلب سداد
    </a>
@endif

@if ($isPendingApproval && auth()->user()->isAdmin())
    <form
        method="POST"
        action="{{ route('admin.installments.approve', ['installment' => $installment->id]) }}"
        onsubmit="return confirm('هل أنت متأكد من تأكيد الدفع وتحويل المبلغ من محفظة المدين إلى الدائن؟')"
        style="display:inline;"
    >
        @csrf
        <button type="submit" class="btn-success btn-sm">
            <i class="fas fa-check-circle"></i> تأكيد الدفع
        </button>
    </form>
@endif

@if ($isPendingApproval && auth()->user()->isDebtor() && $debtorId === (int) auth()->id())
    <span style="color:var(--info);font-size:0.8rem;margin-right:6px;">
        <i class="fas fa-clock"></i> بانتظار تأكيد الإدارة
    </span>
@endif

@if ($installment->status === \App\Models\Installment::STATUS_PAID)
    <span style="color:var(--success);font-size:0.8rem;">
        <i class="fas fa-check-circle"></i> مسدد
    </span>
@endif
