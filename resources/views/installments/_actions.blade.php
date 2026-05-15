{{-- resources/views/installments/_actions.blade.php --}}
{{-- $installment must be passed into this partial --}}

@php
    $isPendingApproval = $installment->status === \App\Models\Installment::STATUS_PENDING_APPROVAL;
    $canPay = in_array($installment->status, [
        \App\Models\Installment::STATUS_PENDING,
        \App\Models\Installment::STATUS_OVERDUE,
    ]);
@endphp

{{-- ── Status badge ─────────────────────────────────────────────────────── --}}
<span class="badge bg-{{ $installment->status_color }}">
    {{ $installment->status_arabic }}
</span>

{{-- ── Pay button (hidden when pending_approval or already paid) ──────── --}}
@if ($canPay && $installment->debt->debtor_id === auth()->id())
    <button
        type="button"
        class="btn btn-primary btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#payModal{{ $installment->id }}"
    >
        دفع القسط
    </button>

    {{-- Pay modal --}}
    <div class="modal fade" id="payModal{{ $installment->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('installments.pay', $installment) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تسجيل دفع القسط</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">رقم المرجع / الإيصال <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="reference_number"
                                class="form-control @error('reference_number') is-invalid @enderror"
                                value="{{ old('reference_number') }}"
                                required
                            >
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إرسال طلب الدفع</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- ── Admin "Confirm Payment" button (only for pending_approval) ──────── --}}
@if ($isPendingApproval && auth()->user()?->role === 'admin')
    <form
        method="POST"
        action="{{ route('admin.installments.approve', $installment) }}"
        onsubmit="return confirm('هل أنت متأكد من تأكيد الدفع وتحويل المبلغ؟')"
        class="d-inline"
    >
        @csrf
        <button type="submit" class="btn btn-success btn-sm">
            <i class="fas fa-check-circle me-1"></i>
            تأكيد الدفع
        </button>
    </form>
@endif

{{-- ── Info notice for debtor while waiting ───────────────────────────── --}}
@if ($isPendingApproval && $installment->debt->debtor_id === auth()->id())
    <span class="text-info small">
        <i class="fas fa-clock me-1"></i>
        بانتظار تأكيد الأدمن
    </span>
@endif
