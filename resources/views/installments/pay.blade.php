@extends('layouts.app')

@section('title', 'طلب سداد قسط')
@section('page-title', 'طلب سداد قسط')
@section('page-subtitle', 'يُرسل الطلب للإدارة للموافقة وتحويل المبلغ من محفظتك')

@section('content')
<div class="page-content">
<div style="max-width:560px;margin:0 auto;">

    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3><i class="fas fa-receipt" style="color:var(--primary);"></i> &nbsp;تفاصيل القسط</h3>
            <span class="badge badge-{{ $installment->status_color }}">{{ $installment->status_arabic }}</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">رقم القسط</div>
                <div style="font-weight:700;font-size:1.1rem;color:var(--primary);">#{{ $installment->installment_number }}</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">قيمة القسط</div>
                <div style="font-weight:700;">{{ number_format($installment->amount, 2) }} ج.م</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">المتبقي للسداد</div>
                <div style="font-weight:900;font-size:1.1rem;color:var(--warning);">{{ number_format($installment->remaining_amount, 2) }} ج.م</div>
            </div>
        </div>
        <div style="margin-top:14px;font-size:0.82rem;color:var(--muted);">
            الدين: <strong style="color:var(--text);">{{ $installment->debt->reference_number }}</strong>
        </div>
    </div>

    <div class="alert alert-info" style="margin-bottom:16px;font-size:0.88rem;">
        <i class="fas fa-info-circle"></i>
        لن يُخصم المبلغ من محفظتك الآن. بعد موافقة الإدارة سيتم التحويل تلقائياً إلى محفظة الدائن.
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-paper-plane" style="color:var(--success);"></i> &nbsp;إرسال طلب السداد</h3>
        </div>

        <form action="{{ route('installments.pay.post', ['installment' => $installment->id]) }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">رقم المرجع / الإيصال *</label>
                <input type="text" name="reference_number"
                    class="form-control {{ $errors->has('reference_number') ? 'is-invalid' : '' }}"
                    value="{{ old('reference_number') }}"
                    placeholder="رقم إيصال التحويل أو المرجع البنكي"
                    required>
                @error('reference_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات (اختياري)</label>
                <textarea name="notes" class="form-control" style="resize:vertical;min-height:80px;"
                    placeholder="أي تفاصيل إضافية للإدارة...">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <a href="{{ route('debts.show', $installment->debt_id) }}" class="btn-secondary">
                    <i class="fas fa-times"></i> إلغاء
                </a>
                <button type="submit" class="btn-success">
                    <i class="fas fa-paper-plane"></i> إرسال للموافقة
                </button>
            </div>
        </form>
    </div>

</div>
</div>
@endsection
