@extends('layouts.app')

@section('title', 'تفاصيل الطلب #' . $request->id)
@section('page-title', 'تفاصيل طلبي')
@section('page-subtitle', 'طلب #' . $request->id . ' — ' . $request->title)

@section('content')
<div class="page-content">
<div style="max-width:680px;margin:0 auto;">

    <div style="margin-bottom:20px;">
        <a href="{{ route('debt-requests.index') }}" class="btn-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> العودة لطلباتي
        </a>
    </div>

    {{-- ════ تفاصيل الطلب ════ --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3>
                <i class="fas fa-file-invoice-dollar" style="color:var(--primary);"></i>
                &nbsp;تفاصيل الطلب
            </h3>
            <span class="badge badge-{{ $request->status_color }}">
                {{ $request->status_arabic }}
            </span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">عنوان الطلب</div>
                <div style="font-weight:700;font-size:1rem;">{{ $request->title }}</div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">تاريخ الطلب</div>
                <div style="font-weight:700;">{{ $request->created_at->format('Y-m-d H:i') }}</div>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">المبلغ المطلوب</div>
                <div style="font-weight:900;font-size:1.1rem;color:var(--primary);">
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
                <div style="line-height:1.7;font-size:.92rem;">{{ $request->description }}</div>
            </div>
        @endif
    </div>

    {{-- ════ نتيجة المراجعة ════ --}}
    @if($request->isApproved())
        <div class="card" style="margin-bottom:20px;border-color:rgba(46,204,113,.3);">
            <div class="card-header">
                <h3 style="color:var(--success);">
                    <i class="fas fa-check-circle"></i> &nbsp;تمت الموافقة على طلبك ✅
                </h3>
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px;">
                <div>
                    <div style="font-size:.72rem;color:var(--muted);">المبلغ المعتمد</div>
                    <div style="font-weight:900;font-size:1.1rem;color:var(--success);">
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

            @if($request->admin_notes)
                <div style="background:rgba(46,204,113,.07);border-radius:8px;padding:12px;margin-bottom:14px;">
                    <div style="font-size:.72rem;color:var(--muted);margin-bottom:4px;">ملاحظات الإدارة:</div>
                    <div>{{ $request->admin_notes }}</div>
                </div>
            @endif

            @if($request->debt)
                <a href="{{ route('debts.show', $request->debt->id) }}" class="btn-success">
                    <i class="fas fa-eye"></i> عرض الدين وجدول الأقساط
                </a>
            @endif
        </div>

    @elseif($request->isRejected())
        <div class="card" style="margin-bottom:20px;border-color:rgba(231,76,60,.3);">
            <div class="card-header">
                <h3 style="color:var(--danger);">
                    <i class="fas fa-times-circle"></i> &nbsp;تم رفض الطلب
                </h3>
            </div>

            @if($request->admin_notes)
                <div style="background:rgba(231,76,60,.07);border-radius:8px;padding:14px;">
                    <div style="font-size:.72rem;color:var(--muted);margin-bottom:6px;">سبب الرفض:</div>
                    <div style="line-height:1.7;">{{ $request->admin_notes }}</div>
                </div>
            @endif

            <div style="margin-top:16px;">
                <a href="{{ route('debt-requests.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i> تقديم طلب جديد
                </a>
            </div>
        </div>

    @else
        {{-- قيد المراجعة --}}
        <div class="card" style="border-color:rgba(243,156,18,.3);">
            <div style="text-align:center;padding:28px 20px;">
                <i class="fas fa-clock"
                   style="font-size:3rem;color:var(--warning);margin-bottom:14px;display:block;
                          animation:pulse 2s infinite;"></i>
                <h3 style="margin-bottom:8px;color:var(--text);">طلبك قيد المراجعة</h3>
                <p style="color:var(--muted);font-size:.88rem;">
                    سيتم إشعارك فور اتخاذ قرار من قبل الإدارة.
                </p>
            </div>
        </div>

        <style>
        @keyframes pulse {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:.6; transform:scale(1.05); }
        }
        </style>
    @endif

</div>
</div>
@endsection
