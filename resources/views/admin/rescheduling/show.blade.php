@extends('layouts.app')

@section('title', 'مراجعة طلب إعادة الجدولة')
@section('page-title', 'مراجعة طلب إعادة جدولة')
@section('page-subtitle', 'راجع وضع الدين وأدخل شروط إعادة الجدولة الجديدة')

@section('content')
<div class="page-content">
<div style="max-width:800px;margin:0 auto;">

    {{-- معلومات الدين الحالي --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3><i class="fas fa-file-invoice-dollar" style="color:var(--warning);"></i> &nbsp;وضع الدين الحالي</h3>
            <span class="badge badge-{{ $reschedule->debt->status_color }}">{{ $reschedule->debt->status_arabic }}</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px;">
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">رقم الدين</div>
                <div style="font-weight:700;color:var(--primary);">{{ $reschedule->debt->reference_number }}</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">المبلغ الأصلي</div>
                <div style="font-weight:700;">{{ number_format($reschedule->debt->principal_amount, 2) }} ج.م</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">نسبة الفائدة الحالية</div>
                <div style="font-weight:700;color:var(--warning);">{{ $reschedule->debt->interest_rate }}%</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">الرصيد غير المسدد</div>
                <div style="font-weight:900;font-size:1.1rem;color:var(--danger);">{{ number_format($reschedule->outstanding_balance, 2) }} ج.م</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">إجمالي المدفوع</div>
                <div style="font-weight:700;color:var(--success);">{{ number_format($reschedule->debt->total_paid, 2) }} ج.م</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">الأشهر المتبقية</div>
                <div style="font-weight:700;">{{ $reschedule->remaining_installments }} شهر</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">القسط الحالي</div>
                <div style="font-weight:700;">{{ number_format($reschedule->debt->monthly_installment, 2) }} ج.م</div>
            </div>
            <div>
                <div style="font-size:0.72rem;color:var(--muted);">نسبة الإنجاز</div>
                <div style="font-weight:700;color:var(--primary);">{{ $reschedule->debt->progress_percentage }}%</div>
            </div>
        </div>

        {{-- شريط التقدم --}}
        <div>
            <div style="display:flex;justify-content:space-between;font-size:0.78rem;color:var(--muted);margin-bottom:6px;">
                <span>مسدد</span>
                <span>{{ $reschedule->debt->progress_percentage }}%</span>
            </div>
            <div class="progress">
                <div class="progress-bar success" style="width:{{ $reschedule->debt->progress_percentage }}%;"></div>
            </div>
        </div>
    </div>

    {{-- سبب طلب إعادة الجدولة --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3><i class="fas fa-user" style="color:var(--info);"></i> &nbsp;{{ $reschedule->user->name }} - سبب الطلب</h3>
            <span style="font-size:0.8rem;color:var(--muted);">{{ $reschedule->created_at->format('Y-m-d') }}</span>
        </div>
        <div style="background:rgba(255,255,255,0.04);border-radius:8px;padding:14px;line-height:1.7;">
            {{ $reschedule->reason }}
        </div>
    </div>

    @if($reschedule->isPending())
    {{-- نموذج قرار إعادة الجدولة --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- الموافقة --}}
        <div class="card" style="border-color:rgba(46,204,113,0.3);">
            <div class="card-header">
                <h3 style="color:var(--success);"><i class="fas fa-check-circle"></i> &nbsp;الموافقة وإعادة الجدولة</h3>
            </div>

            <div style="background:rgba(108,99,255,0.08);border-radius:8px;padding:12px;margin-bottom:16px;font-size:0.82rem;">
                <div style="font-weight:700;color:var(--primary);margin-bottom:6px;"><i class="fas fa-info-circle"></i> آلية إعادة الجدولة:</div>
                <div style="color:var(--muted);line-height:1.6;">
                    الرصيد المتبقي (<strong style="color:var(--text);">{{ number_format($reschedule->outstanding_balance, 2) }} ج.م</strong>)
                    سيصبح أصل الدين الجديد، وستُطبق عليه الفائدة الجديدة لتوليد جدول أقساط جديد.
                    الأقساط القديمة غير المسددة ستُلغى تلقائياً.
                </div>
            </div>

            <form action="{{ route('admin.rescheduling.approve', $reschedule->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">نسبة الفائدة الجديدة (%) *</label>
                    <input type="number" name="new_interest_rate"
                        class="form-control {{ $errors->has('new_interest_rate') ? 'is-invalid' : '' }}"
                        value="{{ old('new_interest_rate', $reschedule->debt->interest_rate) }}"
                        min="0" max="100" step="0.01"
                        id="new_rate" required>
                    @error('new_interest_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">عدد الأشهر الجديدة *</label>
                    <input type="number" name="new_months"
                        class="form-control {{ $errors->has('new_months') ? 'is-invalid' : '' }}"
                        value="{{ old('new_months', $reschedule->remaining_installments) }}"
                        min="1" max="120"
                        id="new_months" required>
                    @error('new_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- معاينة --}}
                <div id="reschedule-preview" style="background:rgba(46,204,113,0.08);border:1px solid rgba(46,204,113,0.2);border-radius:8px;padding:12px;margin-bottom:14px;font-size:0.82rem;">
                    <div style="font-weight:700;color:var(--success);margin-bottom:8px;"><i class="fas fa-calculator"></i> الجدول الجديد:</div>
                    <div style="display:flex;flex-direction:column;gap:4px;color:var(--muted);">
                        <div>الرصيد (أصل جديد): <strong style="color:var(--text);">{{ number_format($reschedule->outstanding_balance, 2) }} ج.م</strong></div>
                        <div>الفائدة الجديدة: <strong id="rs-interest" style="color:var(--text);">-</strong></div>
                        <div>الإجمالي الجديد: <strong id="rs-total" style="color:var(--success);">-</strong></div>
                        <div>القسط الشهري الجديد: <strong id="rs-monthly" style="color:var(--primary);font-size:1rem;">-</strong></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <textarea name="admin_notes" class="form-control" style="resize:vertical;min-height:70px;"
                        placeholder="ملاحظات للمستخدم...">{{ old('admin_notes') }}</textarea>
                </div>

                <button type="submit" class="btn-success" style="width:100%;">
                    <i class="fas fa-sync-alt"></i> تأكيد إعادة الجدولة
                </button>
            </form>
        </div>

        {{-- الرفض --}}
        <div class="card" style="border-color:rgba(231,76,60,0.3);">
            <div class="card-header">
                <h3 style="color:var(--danger);"><i class="fas fa-times-circle"></i> &nbsp;رفض الطلب</h3>
            </div>
            <form action="{{ route('admin.rescheduling.reject', $reschedule->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">سبب الرفض *</label>
                    <textarea name="admin_notes" class="form-control"
                        style="resize:vertical;min-height:180px;"
                        placeholder="اكتب سبب الرفض بوضوح..."
                        required>{{ old('admin_notes') }}</textarea>
                </div>
                <button type="submit" class="btn-danger" style="width:100%;"
                    onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">
                    <i class="fas fa-times"></i> رفض الطلب
                </button>
            </form>
        </div>
    </div>

    @else
        <div class="card">
            <div class="card-header">
                <h3>نتيجة المراجعة</h3>
                <span class="badge badge-{{ $reschedule->isApproved() ? 'success' : 'danger' }}">
                    {{ $reschedule->status_arabic }}
                </span>
            </div>
            @if($reschedule->isApproved())
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                    <div>
                        <div style="font-size:0.75rem;color:var(--muted);">نسبة الفائدة الجديدة</div>
                        <div style="font-weight:900;color:var(--warning);">{{ $reschedule->new_interest_rate }}%</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:var(--muted);">الأشهر الجديدة</div>
                        <div style="font-weight:900;">{{ $reschedule->new_months }} شهر</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:var(--muted);">القسط الشهري الجديد</div>
                        <div style="font-weight:900;color:var(--primary);">{{ number_format($reschedule->new_monthly_installment, 2) }} ج.م</div>
                    </div>
                </div>
            @endif
            @if($reschedule->admin_notes)
                <div style="background:rgba(255,255,255,0.04);border-radius:8px;padding:12px;margin-top:14px;">
                    <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px;">ملاحظات المدير:</div>
                    <div>{{ $reschedule->admin_notes }}</div>
                </div>
            @endif
        </div>
    @endif

    <div style="margin-top:16px;">
        <a href="{{ route('admin.rescheduling.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-right"></i> العودة للقائمة
        </a>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
const balance = {{ $reschedule->outstanding_balance }};

function updateReschedulePreview() {
    const rate   = parseFloat(document.getElementById('new_rate').value) || 0;
    const months = parseInt(document.getElementById('new_months').value) || 0;
    if (months > 0) {
        const interest = balance * (rate / 100);
        const total    = balance + interest;
        const monthly  = total / months;
        document.getElementById('rs-interest').textContent = interest.toLocaleString('ar-EG',{minimumFractionDigits:2}) + ' ج.م';
        document.getElementById('rs-total').textContent    = total.toLocaleString('ar-EG',{minimumFractionDigits:2}) + ' ج.م';
        document.getElementById('rs-monthly').textContent  = monthly.toLocaleString('ar-EG',{minimumFractionDigits:2}) + ' ج.م';
    }
}

document.getElementById('new_rate')?.addEventListener('input', updateReschedulePreview);
document.getElementById('new_months')?.addEventListener('input', updateReschedulePreview);
updateReschedulePreview();
</script>
@endpush
