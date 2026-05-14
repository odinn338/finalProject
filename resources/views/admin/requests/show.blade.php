@extends('layouts.app')

@section('title', 'مراجعة الطلب #' . $request->id)
@section('page-title', 'مراجعة طلب دين')
@section('page-subtitle', 'طلب #' . $request->id . ' — ' . ($request->user?->name ?? 'مستخدم محذوف'))

@section('content')
<div class="page-content">
<div style="max-width:820px;margin:0 auto;">

    {{-- رابط العودة --}}
    <div style="margin-bottom:20px;">
        <a href="{{ route('admin.requests.index') }}" class="btn-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> العودة للقائمة
        </a>
    </div>

    {{-- ════ معلومات المستخدم ════ --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3>
                <i class="fas fa-user-circle" style="color:var(--primary);"></i>
                &nbsp;مقدم الطلب
            </h3>
            <span class="badge badge-{{ $request->status_color }}">
                {{ $request->status_arabic }}
            </span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">الاسم الكامل</div>
                <div style="font-weight:700;">{{ $request->user?->name ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">البريد الإلكتروني</div>
                <div style="font-weight:700;">{{ $request->user?->email ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">رقم الهاتف</div>
                <div style="font-weight:700;">{{ $request->user?->phone ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">درجة الائتمان</div>
                <div style="font-weight:700;color:var(--success);">
                    {{ $request->user?->credit_score ?? '—' }} / 100
                </div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">تاريخ الطلب</div>
                <div style="font-weight:700;">{{ $request->created_at->format('Y-m-d H:i') }}</div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">الديون النشطة</div>
                <div style="font-weight:700;">
                    {{ $request->user?->debts()->where('status','active')->count() ?? 0 }} دين
                </div>
            </div>
        </div>
    </div>

    {{-- ════ تفاصيل الطلب ════ --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3>
                <i class="fas fa-file-invoice-dollar" style="color:var(--warning);"></i>
                &nbsp;تفاصيل الطلب
            </h3>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">عنوان الطلب</div>
                <div style="font-weight:700;font-size:1rem;">{{ $request->title }}</div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">المبلغ المطلوب</div>
                <div style="font-weight:900;font-size:1.2rem;color:var(--primary);">
                    {{ number_format($request->requested_amount, 2) }} ج.م
                </div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">المدة المطلوبة</div>
                <div style="font-weight:700;">{{ $request->requested_months }} شهر</div>
            </div>
        </div>

        @if($request->description)
            <div style="background:rgba(255,255,255,.04);border-radius:8px;padding:14px;">
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:6px;">الغرض من القرض:</div>
                <div style="font-size:.92rem;line-height:1.7;">{{ $request->description }}</div>
            </div>
        @endif
    </div>

    {{-- ════ قرار المدير ════ --}}
    @if($request->isPending())

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

            {{-- ── الموافقة ── --}}
            <div class="card" style="border-color:rgba(46,204,113,.3);">
                <div class="card-header">
                    <h3 style="color:var(--success);">
                        <i class="fas fa-check-circle"></i> &nbsp;الموافقة على الطلب
                    </h3>
                </div>

                <form action="{{ route('admin.requests.approve', $request->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">المبلغ المعتمد (ج.م) *</label>
                        <input type="number" name="approved_amount"
                               class="form-control {{ $errors->has('approved_amount') ? 'is-invalid' : '' }}"
                               value="{{ old('approved_amount', $request->requested_amount) }}"
                               min="1" step="0.01" required
                               id="inp_amount">
                        @error('approved_amount')
                            <div class="invalid-feedback"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">نسبة الفائدة (%) *</label>
                        <input type="number" name="interest_rate"
                               class="form-control {{ $errors->has('interest_rate') ? 'is-invalid' : '' }}"
                               value="{{ old('interest_rate', 15) }}"
                               min="0" max="100" step="0.01"
                               id="inp_rate" required>
                        @error('interest_rate')
                            <div class="invalid-feedback"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">عدد الأشهر *</label>
                        <input type="number" name="approved_months"
                               class="form-control {{ $errors->has('approved_months') ? 'is-invalid' : '' }}"
                               value="{{ old('approved_months', $request->requested_months) }}"
                               min="1" max="120"
                               id="inp_months" required>
                        @error('approved_months')
                            <div class="invalid-feedback"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- معاينة الحساب الفوري --}}
                    <div id="calc-preview"
                         style="background:rgba(46,204,113,.08);border:1px solid rgba(46,204,113,.2);
                                border-radius:8px;padding:12px;margin-bottom:16px;font-size:.82rem;">
                        <div style="font-weight:700;color:var(--success);margin-bottom:8px;">
                            <i class="fas fa-calculator"></i> معاينة الحساب:
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;color:var(--muted);">
                            <div>الفائدة: <strong id="p-interest" style="color:var(--text);">—</strong></div>
                            <div>الإجمالي: <strong id="p-total" style="color:var(--success);">—</strong></div>
                            <div style="grid-column:span 2;">
                                القسط الشهري:
                                <strong id="p-monthly"
                                        style="color:var(--primary);font-size:1rem;">—</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ملاحظات (اختياري)</label>
                        <textarea name="admin_notes" class="form-control"
                                  style="resize:vertical;min-height:70px;"
                                  placeholder="ملاحظات للمستخدم...">{{ old('admin_notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn-success" style="width:100%;">
                        <i class="fas fa-check"></i> تأكيد الموافقة وإنشاء الأقساط
                    </button>
                </form>
            </div>

            {{-- ── الرفض ── --}}
            <div class="card" style="border-color:rgba(231,76,60,.3);">
                <div class="card-header">
                    <h3 style="color:var(--danger);">
                        <i class="fas fa-times-circle"></i> &nbsp;رفض الطلب
                    </h3>
                </div>

                <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">سبب الرفض *</label>
                        <textarea name="admin_notes"
                                  class="form-control {{ $errors->has('admin_notes') ? 'is-invalid' : '' }}"
                                  style="resize:vertical;min-height:160px;"
                                  placeholder="اكتب سبب الرفض بوضوح ليطّلع عليه المستخدم..."
                                  required>{{ old('admin_notes') }}</textarea>
                        @error('admin_notes')
                            <div class="invalid-feedback"><i class="fas fa-times-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-danger" style="font-size:.8rem;margin-bottom:16px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        هذا الإجراء لا يمكن التراجع عنه.
                    </div>

                    <button type="submit" class="btn-danger" style="width:100%;"
                            onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">
                        <i class="fas fa-times"></i> تأكيد الرفض
                    </button>
                </form>
            </div>

        </div>

    @else
        {{-- ════ قرار سابق ════ --}}
        <div class="card"
             style="border-color:{{ $request->isApproved() ? 'rgba(46,204,113,.3)' : 'rgba(231,76,60,.3)' }}">
            <div class="card-header">
                <h3>نتيجة المراجعة</h3>
                <span class="badge badge-{{ $request->status_color }}">{{ $request->status_arabic }}</span>
            </div>

            @if($request->isApproved())
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px;">
                    <div>
                        <div style="font-size:.72rem;color:var(--muted);">المبلغ المعتمد</div>
                        <div style="font-weight:900;color:var(--success);">
                            {{ number_format($request->approved_amount, 2) }} ج.م
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;color:var(--muted);">نسبة الفائدة</div>
                        <div style="font-weight:900;color:var(--warning);">{{ $request->interest_rate }}%</div>
                    </div>
                    <div>
                        <div style="font-size:.72rem;color:var(--muted);">المدة المعتمدة</div>
                        <div style="font-weight:900;">{{ $request->approved_months }} شهر</div>
                    </div>
                </div>

                @if($request->debt)
                    <a href="{{ route('debts.show', $request->debt->id) }}" class="btn-primary btn-sm">
                        <i class="fas fa-eye"></i> عرض الدين المنشأ
                    </a>
                @endif
            @endif

            @if($request->admin_notes)
                <div style="background:rgba(255,255,255,.04);border-radius:8px;padding:12px;margin-top:14px;">
                    <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">ملاحظات المدير:</div>
                    <div style="line-height:1.6;">{{ $request->admin_notes }}</div>
                </div>
            @endif

            <div style="margin-top:12px;font-size:.78rem;color:var(--muted);">
                راجعه: <strong>{{ $request->reviewer?->name ?? '—' }}</strong>
                في {{ $request->reviewed_at?->format('Y-m-d H:i') ?? '—' }}
            </div>
        </div>
    @endif

</div>
</div>
@endsection

@push('scripts')
<script>
function updateCalc() {
    const principal = parseFloat(document.getElementById('inp_amount')?.value)  || 0;
    const rate      = parseFloat(document.getElementById('inp_rate')?.value)    || 0;
    const months    = parseInt(document.getElementById('inp_months')?.value)    || 0;

    if (principal > 0 && months > 0) {
        const interest = principal * (rate / 100);
        const total    = principal + interest;
        const monthly  = total / months;

        const fmt = n => n.toLocaleString('ar-EG', { minimumFractionDigits: 2 }) + ' ج.م';
        document.getElementById('p-interest').textContent = fmt(interest);
        document.getElementById('p-total').textContent    = fmt(total);
        document.getElementById('p-monthly').textContent  = fmt(monthly);
    }
}

['inp_amount','inp_rate','inp_months'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', updateCalc);
});
updateCalc();
</script>
@endpush
