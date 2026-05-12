@extends('layouts.app')

@section('title', 'مراجعة الطلب')
@section('page-title', 'مراجعة طلب دين')
@section('page-subtitle', 'راجع تفاصيل الطلب وأدخل قرارك')

@section('content')
<div class="page-content">
<div style="max-width:760px;margin:0 auto;">

    {{-- معلومات مقدم الطلب --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3><i class="fas fa-user-circle" style="color:var(--primary);"></i> &nbsp;معلومات مقدم الطلب</h3>
            <span class="badge badge-{{ $request->status_color }}">{{ $request->status_arabic }}</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">الاسم الكامل</div>
                <div style="font-weight:700;">{{ $request->user->name }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">البريد الإلكتروني</div>
                <div style="font-weight:700;">{{ $request->user->email }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">رقم الهاتف</div>
                <div style="font-weight:700;">{{ $request->user->phone ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">درجة الائتمان</div>
                <div style="font-weight:700;color:var(--success);">{{ $request->user->credit_score }} / 100</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">تاريخ الطلب</div>
                <div style="font-weight:700;">{{ $request->created_at->format('Y-m-d') }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">الديون النشطة</div>
                <div style="font-weight:700;">{{ $request->user->debts()->where('status','active')->count() }} دين</div>
            </div>
        </div>
    </div>

    {{-- تفاصيل الطلب --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3><i class="fas fa-file-invoice-dollar" style="color:var(--warning);"></i> &nbsp;تفاصيل الطلب</h3>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">عنوان الطلب</div>
                <div style="font-weight:700;font-size:1rem;">{{ $request->title }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">المبلغ المطلوب</div>
                <div style="font-weight:900;font-size:1.2rem;color:var(--primary);">{{ number_format($request->requested_amount, 2) }} ج.م</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">المدة المطلوبة</div>
                <div style="font-weight:700;">{{ $request->requested_months }} شهر</div>
            </div>
        </div>
        @if($request->description)
            <div style="background:rgba(255,255,255,0.04);border-radius:8px;padding:14px;">
                <div style="font-size:0.75rem;color:var(--muted);margin-bottom:6px;">الغرض من القرض:</div>
                <div style="font-size:0.9rem;line-height:1.6;">{{ $request->description }}</div>
            </div>
        @endif
    </div>

    @if($request->isPending())

    {{-- نموذج قرار المدير --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- الموافقة --}}
        <div class="card" style="border-color:rgba(46,204,113,0.3);">
            <div class="card-header">
                <h3 style="color:var(--success);"><i class="fas fa-check-circle"></i> &nbsp;الموافقة على الطلب</h3>
            </div>
            <form action="{{ route('admin.requests.approve', $request->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">المبلغ المعتمد (ج.م) *</label>
                    <input type="number" name="approved_amount"
                        class="form-control {{ $errors->has('approved_amount') ? 'is-invalid' : '' }}"
                        value="{{ old('approved_amount', $request->requested_amount) }}"
                        min="1" step="0.01" required>
                    @error('approved_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">نسبة الفائدة السنوية (%) *</label>
                    <input type="number" name="interest_rate"
                        class="form-control {{ $errors->has('interest_rate') ? 'is-invalid' : '' }}"
                        value="{{ old('interest_rate', 15) }}"
                        min="0" max="100" step="0.01"
                        id="interest_rate_input" required>
                    @error('interest_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">عدد الأشهر *</label>
                    <input type="number" name="approved_months"
                        class="form-control {{ $errors->has('approved_months') ? 'is-invalid' : '' }}"
                        value="{{ old('approved_months', $request->requested_months) }}"
                        min="1" max="120" id="months_input" required>
                    @error('approved_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- معاينة الحساب --}}
                <div id="calc-preview" style="background:rgba(46,204,113,0.08);border:1px solid rgba(46,204,113,0.2);border-radius:8px;padding:12px;margin-bottom:16px;font-size:0.82rem;">
                    <div style="font-weight:700;color:var(--success);margin-bottom:8px;"><i class="fas fa-calculator"></i> معاينة الحساب:</div>
                    <div style="display:flex;flex-direction:column;gap:4px;color:var(--muted);">
                        <div>المبلغ الأصلي: <strong id="p-principal" style="color:var(--text);">-</strong></div>
                        <div>الفائدة: <strong id="p-interest" style="color:var(--text);">-</strong></div>
                        <div>الإجمالي: <strong id="p-total" style="color:var(--success);"></strong></div>
                        <div>القسط الشهري: <strong id="p-monthly" style="color:var(--primary);font-size:1rem;"></strong></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <textarea name="admin_notes" class="form-control" style="resize:vertical;min-height:70px;"
                        placeholder="أي ملاحظات للمستخدم...">{{ old('admin_notes') }}</textarea>
                </div>

                <button type="submit" class="btn-success" style="width:100%;">
                    <i class="fas fa-check"></i> تأكيد الموافقة وإنشاء الأقساط
                </button>
            </form>
        </div>

        {{-- الرفض --}}
        <div class="card" style="border-color:rgba(231,76,60,0.3);">
            <div class="card-header">
                <h3 style="color:var(--danger);"><i class="fas fa-times-circle"></i> &nbsp;رفض الطلب</h3>
            </div>
            <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">سبب الرفض *</label>
                    <textarea name="admin_notes" class="form-control {{ $errors->has('admin_notes') ? 'is-invalid' : '' }}"
                        style="resize:vertical;min-height:140px;"
                        placeholder="اكتب سبب الرفض هنا ليطّلع عليه المستخدم..."
                        required>{{ old('admin_notes') }}</textarea>
                    @error('admin_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div style="padding-top:8px;">
                    <div class="alert alert-danger" style="font-size:0.82rem;margin-bottom:16px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        هذا الإجراء لا يمكن التراجع عنه. سيتم إشعار المستخدم بالرفض.
                    </div>
                    <button type="submit" class="btn-danger" style="width:100%;"
                        onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">
                        <i class="fas fa-times"></i> تأكيد الرفض
                    </button>
                </div>
            </form>
        </div>

    </div>

    @else
        {{-- قرار سابق --}}
        <div class="card" style="border-color:{{ $request->isApproved() ? 'rgba(46,204,113,0.3)' : 'rgba(231,76,60,0.3)' }}">
            <div class="card-header">
                <h3>نتيجة المراجعة</h3>
                <span class="badge badge-{{ $request->status_color }}">{{ $request->status_arabic }}</span>
            </div>
            @if($request->isApproved())
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px;">
                    <div>
                        <div style="font-size:0.75rem;color:var(--muted);">المبلغ المعتمد</div>
                        <div style="font-weight:900;color:var(--success);">{{ number_format($request->approved_amount, 2) }} ج.م</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:var(--muted);">نسبة الفائدة</div>
                        <div style="font-weight:900;color:var(--warning);">{{ $request->interest_rate }}%</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:var(--muted);">المدة المعتمدة</div>
                        <div style="font-weight:900;">{{ $request->approved_months }} شهر</div>
                    </div>
                </div>
                @if($request->debt)
                    <a href="{{ route('debts.show', $request->debt->id) }}" class="btn-primary btn-sm">
                        <i class="fas fa-eye"></i> عرض الدين
                    </a>
                @endif
            @endif
            @if($request->admin_notes)
                <div style="background:rgba(255,255,255,0.04);border-radius:8px;padding:12px;margin-top:12px;">
                    <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">ملاحظات المدير:</div>
                    <div>{{ $request->admin_notes }}</div>
                </div>
            @endif
        </div>
    @endif

    <div style="margin-top:16px;">
        <a href="{{ route('admin.requests.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-right"></i> العودة للقائمة
        </a>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
function updatePreview() {
    const principal = parseFloat(document.querySelector('[name="approved_amount"]').value) || 0;
    const rate      = parseFloat(document.getElementById('interest_rate_input').value) || 0;
    const months    = parseInt(document.getElementById('months_input').value) || 0;

    if (principal > 0 && months > 0) {
        const interest = principal * (rate / 100);
        const total    = principal + interest;
        const monthly  = total / months;

        document.getElementById('p-principal').textContent = principal.toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';
        document.getElementById('p-interest').textContent  = interest.toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';
        document.getElementById('p-total').textContent     = total.toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';
        document.getElementById('p-monthly').textContent   = monthly.toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';
    }
}

['approved_amount','interest_rate_input','months_input'].forEach(id => {
    const el = document.getElementById(id) || document.querySelector('[name="'+id+'"]');
    if (el) el.addEventListener('input', updatePreview);
});

document.querySelector('[name="approved_amount"]')?.addEventListener('input', updatePreview);
updatePreview();
</script>
@endpush