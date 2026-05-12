@extends('layouts.app')

@section('title', 'طلب دين جديد')
@section('page-title', 'تقديم طلب دين جديد')
@section('page-subtitle', 'أملأ النموذج أدناه وسيتم مراجعته من قبل الإدارة')

@section('content')
<div class="page-content">

    <div style="max-width:680px;margin:0 auto;">

        {{-- معلومات توضيحية --}}
        <div class="alert alert-info" style="margin-bottom:24px;">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>كيف يعمل النظام؟</strong><br>
                <span style="font-size:0.85rem;opacity:0.9;">
                    ١. أملأ النموذج أدناه بتفاصيل طلبك.<br>
                    ٢. يراجع المدير الطلب ويوافق عليه بتحديد نسبة الفائدة والمدة.<br>
                    ٣. عند الموافقة، يُنشأ جدول الأقساط تلقائياً.
                </span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar" style="color:var(--primary);"></i> &nbsp;تفاصيل الطلب</h3>
            </div>

            <form action="{{ route('debt-requests.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="title">عنوان الطلب *</label>
                    <div style="position:relative;">
                        <i class="fas fa-tag" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--muted);"></i>
                        <input type="text" id="title" name="title"
                            class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                            style="padding-right:40px;"
                            value="{{ old('title') }}"
                            placeholder="مثال: قرض شراء سيارة، قرض علاجي..."
                            required>
                    </div>
                    @error('title')<div class="invalid-feedback"><i class="fas fa-times-circle"></i> {{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">الغرض من القرض (اختياري)</label>
                    <textarea id="description" name="description"
                        class="form-control"
                        style="resize:vertical;min-height:90px;"
                        placeholder="اشرح الغرض من الطلب وكيف ستستخدم المبلغ...">{{ old('description') }}</textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                    <div class="form-group">
                        <label class="form-label" for="requested_amount">المبلغ المطلوب (ج.م) *</label>
                        <div style="position:relative;">
                            <i class="fas fa-pound-sign" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--muted);"></i>
                            <input type="number" id="requested_amount" name="requested_amount"
                                class="form-control {{ $errors->has('requested_amount') ? 'is-invalid' : '' }}"
                                style="padding-right:40px;"
                                value="{{ old('requested_amount') }}"
                                min="100" step="0.01"
                                placeholder="10000.00"
                                required>
                        </div>
                        @error('requested_amount')<div class="invalid-feedback"><i class="fas fa-times-circle"></i> {{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="requested_months">مدة السداد المطلوبة (شهر) *</label>
                        <div style="position:relative;">
                            <i class="fas fa-calendar-alt" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--muted);"></i>
                            <select id="requested_months" name="requested_months"
                                class="form-control {{ $errors->has('requested_months') ? 'is-invalid' : '' }}"
                                style="padding-right:40px;" required>
                                <option value="">-- اختر المدة --</option>
                                @foreach([3,6,9,12,18,24,36,48,60] as $m)
                                    <option value="{{ $m }}" {{ old('requested_months') == $m ? 'selected' : '' }}>
                                        {{ $m }} شهر {{ $m == 12 ? '(سنة)' : ($m == 24 ? '(سنتان)' : ($m == 36 ? '(3 سنوات)' : '')) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('requested_months')<div class="invalid-feedback"><i class="fas fa-times-circle"></i> {{ $message }}</div>@enderror
                    </div>

                </div>

                {{-- حاسبة تقديرية --}}
                <div id="calculator-preview" style="display:none;background:rgba(108,99,255,0.08);border:1px solid rgba(108,99,255,0.2);border-radius:10px;padding:16px;margin-bottom:20px;">
                    <div style="font-size:0.82rem;font-weight:700;color:var(--muted);margin-bottom:10px;">
                        <i class="fas fa-calculator"></i> تقدير تقريبي (بدون فائدة):
                    </div>
                    <div style="display:flex;gap:20px;flex-wrap:wrap;">
                        <div>
                            <div style="font-size:0.75rem;color:var(--muted);">القسط الشهري التقديري</div>
                            <div id="est-monthly" style="font-size:1.1rem;font-weight:900;color:var(--primary);">-</div>
                        </div>
                        <div>
                            <div style="font-size:0.75rem;color:var(--muted);">المبلغ الإجمالي</div>
                            <div id="est-total" style="font-size:1.1rem;font-weight:900;color:var(--text);">-</div>
                        </div>
                    </div>
                    <div style="font-size:0.75rem;color:var(--muted);margin-top:8px;">
                        * المبلغ الفعلي يتضمن نسبة الفائدة التي يحددها المدير عند الموافقة
                    </div>
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <a href="{{ route('debt-requests.index') }}" class="btn-secondary">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        إرسال الطلب
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const amountInput = document.getElementById('requested_amount');
const monthsSelect = document.getElementById('requested_months');
const preview = document.getElementById('calculator-preview');

function updateCalc() {
    const amount = parseFloat(amountInput.value);
    const months = parseInt(monthsSelect.value);
    if (amount > 0 && months > 0) {
        const monthly = (amount / months).toFixed(2);
        document.getElementById('est-monthly').textContent = parseFloat(monthly).toLocaleString('ar-EG') + ' ج.م';
        document.getElementById('est-total').textContent   = amount.toLocaleString('ar-EG') + ' ج.م';
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

amountInput.addEventListener('input', updateCalc);
monthsSelect.addEventListener('change', updateCalc);
</script>
@endpush
