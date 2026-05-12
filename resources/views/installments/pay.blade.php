@extends('layouts.app')

@section('title', 'تسديد القسط')
@section('page-title', 'تسديد قسط')
@section('page-subtitle', 'أدخل تفاصيل الدفع لتسجيل عملية السداد')

@section('content')
<div class="page-content">
<div style="max-width:560px;margin:0 auto;">

    {{-- تفاصيل القسط --}}
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
                <div style="font-size:0.72rem;color:var(--muted);">تاريخ الاستحقاق</div>
                <div style="font-weight:700;color:{{ $installment->status==='overdue' ? 'var(--danger)' : 'var(--text)' }};">
                    {{ $installment->due_date->format('Y-m-d') }}
                    @if($installment->status === 'overdue')
                        <div style="font-size:0.72rem;color:var(--danger);">متأخر {{ $installment->days_overdue }} يوم</div>
                    @endif
                </div>
            </div>
            @if($installment->penalty_amount > 0)
                <div>
                    <div style="font-size:0.72rem;color:var(--muted);">غرامة التأخير</div>
                    <div style="font-weight:700;color:var(--danger);">{{ number_format($installment->penalty_amount, 2) }} ج.م</div>
                </div>
            @endif
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">المسدد مسبقاً</div>
                <div style="font-weight:700;color:var(--success);">{{ number_format($installment->paid_amount, 2) }} ج.م</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">المتبقي للسداد</div>
                <div style="font-weight:900;font-size:1.1rem;color:var(--warning);">{{ number_format($installment->remaining_amount, 2) }} ج.م</div>
            </div>
        </div>
        <div style="margin-top:14px;">
            <div style="font-size:0.78rem;color:var(--muted);margin-bottom:6px;">
                الدين: <strong style="color:var(--text);">{{ $installment->debt->reference_number }}</strong>
            </div>
        </div>
    </div>

    {{-- نموذج الدفع --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-money-bill-wave" style="color:var(--success);"></i> &nbsp;تفاصيل الدفع</h3>
        </div>

        <form action="{{ route('installments.pay.post', $installment->id) }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">المبلغ المدفوع (ج.م) *</label>
                <div style="position:relative;">
                    <i class="fas fa-pound-sign" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--muted);"></i>
                    <input type="number" name="amount" id="pay_amount"
                        class="form-control {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                        style="padding-right:40px;"
                        value="{{ old('amount', $installment->remaining_amount) }}"
                        min="1" max="{{ $installment->remaining_amount }}" step="0.01"
                        required>
                </div>
                @error('amount')<div class="invalid-feedback"><i class="fas fa-times-circle"></i> {{ $message }}</div>@enderror
                <div style="display:flex;gap:8px;margin-top:8px;">
                    <button type="button" class="btn-secondary btn-sm"
                        onclick="document.getElementById('pay_amount').value = {{ $installment->remaining_amount }}">
                        دفع كامل ({{ number_format($installment->remaining_amount, 2) }})
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">طريقة الدفع *</label>
                <select name="payment_method" class="form-control {{ $errors->has('payment_method') ? 'is-invalid' : '' }}" required>
                    <option value="">-- اختر طريقة الدفع --</option>
                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>نقداً</option>
                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>تحويل بنكي</option>
                    <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>شيك</option>
                </select>
                @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">رقم المرجع / الإيصال (اختياري)</label>
                <input type="text" name="reference_number"
                    class="form-control"
                    value="{{ old('reference_number') }}"
                    placeholder="رقم إيصال الدفع أو التحويل">
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات (اختياري)</label>
                <textarea name="notes" class="form-control" style="resize:vertical;min-height:70px;"
                    placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <a href="{{ route('debts.show', $installment->debt_id) }}" class="btn-secondary">
                    <i class="fas fa-times"></i> إلغاء
                </a>
                <button type="submit" class="btn-success">
                    <i class="fas fa-check"></i> تأكيد السداد
                </button>
            </div>
        </form>
    </div>

</div>
</div>
@endsection
