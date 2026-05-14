@extends('layouts.app')

@section('title', 'لوحة الدائن')
@section('page-title', 'مرحباً، ' . auth()->user()->name)
@section('page-subtitle', 'ملخص التمويل والمحصّل')

@section('content')
<div class="page-content">
    <section class="statistics-section" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card total-debt">
            <div class="stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="stat-content">
                <h3>إجمالي المُموّل</h3>
                <p class="stat-number">{{ number_format($totalLent, 2) }} <small style="font-size:0.55em;">ج.م</small></p>
                <span class="stat-label">قيمة الديون النشطة</span>
            </div>
        </div>
        <div class="stat-card paid">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-content">
                <h3>المحصّل</h3>
                <p class="stat-number">{{ number_format($totalReceived, 2) }} <small style="font-size:0.55em;">ج.م</small></p>
                <span class="stat-label">من سجلات الدفعات</span>
            </div>
        </div>
        <div class="stat-card remaining">
            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            <div class="stat-content">
                <h3>رصيد المحفظة</h3>
                <p class="stat-number">{{ number_format($wallet?->available_balance ?? 0, 2) }} <small style="font-size:0.55em;">ج.م</small></p>
                <span class="stat-label">المتاح للاستخدام</span>
            </div>
        </div>
    </section>

    <section style="padding:20px 32px;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar" style="color:var(--primary);"></i> الديون النشطة</h3>
                <a href="{{ route('debts.index') }}" class="btn-primary btn-sm">عرض الكل</a>
            </div>
            @forelse($activeDebts as $debt)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-weight:700;">{{ $debt->reference_number }}</div>
                        <div style="font-size:0.8rem;color:var(--muted);">المدين: {{ $debt->borrower?->name ?? '—' }}</div>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-weight:700;">{{ number_format($debt->remaining_balance, 2) }} ج.م</div>
                        <a href="{{ route('debts.show', $debt) }}" style="font-size:0.78rem;color:var(--primary);">التفاصيل</a>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:40px;color:var(--muted);">لا توجد ديون نشطة مرتبطة بحسابك بعد.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
