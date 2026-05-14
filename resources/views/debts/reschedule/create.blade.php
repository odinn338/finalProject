@extends('layouts.app')

@section('title', 'طلب إعادة الجدولة')
@section('page-title', 'طلب إعادة جدولة')
@section('page-subtitle', 'دين رقم: ' . $debt->reference_number)

@section('content')
<div class="page-content">
<div style="max-width:640px;margin:0 auto;">

    <div style="margin-bottom:20px;">
        <a href="{{ route('debts.show', $debt->id) }}" class="btn-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> العودة لتفاصيل الدين
        </a>
    </div>

    {{-- ════ ملخص الدين الحالي ════ --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3>
                <i class="fas fa-info-circle" style="color:var(--info);"></i>
                &nbsp;وضعك الحالي
            </h3>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
            <div style="text-align:center;padding:12px;background:rgba(231,76,60,.07);border-radius:8px;">
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">الرصيد المتبقي</div>
                <div style="font-weight:900;color:var(--danger);">
                    {{ number_format($debt->remaining_balance, 2) }} ج.م
                </div>
            </div>
            <div style="text-align:center;padding:12px;background:rgba(108,99,255,.07);border-radius:8px;">
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">القسط الحالي</div>
                <div style="font-weight:900;color:var(--primary);">
                    {{ number_format($debt->monthly_installment, 2) }} ج.م
                </div>
            </div>
            <div style="text-align:center;padding:12px;background:rgba(243,156,18,.07);border-radius:8px;">
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">الأقساط المتبقية</div>
                <div style="font-weight:900;color:var(--warning);">
                    {{ $pendingInstallments }} قسط
                </div>
            </div>
        </div>
    </div>

    {{-- ════ توضيح العملية ════ --}}
    <div class="alert alert-info" style="margin-bottom:20px;flex-direction:column;align-items:flex-start;">
        <div style="font-weight:700;margin-bottom:6px;">
            <i class="fas fa-sync-alt"></i> ماذا يحدث عند إعادة الجدولة؟
        </div>
        <ul style="padding-right:20px;list-style:disc;font-size:.86rem;line-height:1.8;opacity:.9;">
            <li>يُحسَب رصيدك غير المسدد الحالي كأصل دين جديد.</li>
            <li>يحدد المدير نسبة فائدة جديدة وعدد أشهر جديدة.</li>
            <li>تُلغى الأقساط القديمة غير المسددة وتُولَّد أقساط جديدة.</li>
        </ul>
    </div>

    {{-- ════ نموذج الطلب ════ --}}
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-edit" style="color:var(--primary);"></i>
                &nbsp;بيانات طلب إعادة الجدولة
            </h3>
        </div>

        <form action="{{ route('rescheduling.store', $debt->id) }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="reason">
                    سبب طلب إعادة الجدولة *
                    <span style="font-size:.72rem;color:var(--muted);">(20 حرف على الأقل)</span>
                </label>
                <textarea id="reason" name="reason"
                          class="form-control {{ $errors->has('reason') ? 'is-invalid' : '' }}"
                          style="resize:vertical;min-height:140px;"
                          placeholder="اشرح الأسباب التي تجعل السداد صعباً عليك حالياً (فقدان وظيفة، نفقات طارئة، ضائقة مالية...)"
                          required>{{ old('reason') }}</textarea>
                @error('reason')
                    <div class="invalid-feedback">
                        <i class="fas fa-times-circle"></i> {{ $message }}
                    </div>
                @enderror

                <div style="text-align:left;font-size:.72rem;color:var(--muted);margin-top:4px;">
                    <span id="char-count">0</span> حرف
                </div>
            </div>

            <div class="alert alert-warning" style="font-size:.82rem;margin-bottom:20px;">
                <i class="fas fa-exclamation-triangle"></i>
                طلب إعادة الجدولة يُراجَع من قبل الإدارة ولا يمكن إلغاؤه بعد الإرسال.
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <a href="{{ route('debts.show', $debt->id) }}" class="btn-secondary">
                    <i class="fas fa-times"></i> إلغاء
                </a>
                <button type="submit" class="btn-warning">
                    <i class="fas fa-paper-plane"></i> إرسال الطلب
                </button>
            </div>
        </form>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
const textarea  = document.getElementById('reason');
const counter   = document.getElementById('char-count');
textarea?.addEventListener('input', () => {
    counter.textContent = textarea.value.length;
    counter.style.color = textarea.value.length < 20 ? 'var(--danger)' : 'var(--success)';
});
</script>
@endpush
