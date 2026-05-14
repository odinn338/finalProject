@extends('layouts.app')

@section('title', 'لوحة المدين')
@section('page-title', 'مرحباً، ' . auth()->user()->name)
@section('page-subtitle', 'ملخص التزاماتك والأقساط')

@section('content')
<div class="page-content">
    <section class="statistics-section" style="grid-template-columns:repeat(2,1fr);">
        <div class="stat-card total-debt">
            <div class="stat-icon"><i class="fas fa-balance-scale"></i></div>
            <div class="stat-content">
                <h3>إجمالي المتبقي</h3>
                <p class="stat-number">{{ number_format($totalOwed, 2) }} <small style="font-size:0.55em;">ج.م</small></p>
                <span class="stat-label">ديون نشطة</span>
            </div>
        </div>
        <div class="stat-card paid">
            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            <div class="stat-content">
                <h3>رصيد المحفظة</h3>
                <p class="stat-number">{{ number_format($wallet?->available_balance ?? 0, 2) }} <small style="font-size:0.55em;">ج.م</small></p>
                <span class="stat-label">المتاح للسداد</span>
            </div>
        </div>
    </section>

    <section style="padding:20px 32px;display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar"></i> ديوني النشطة</h3>
                <a href="{{ route('debts.index') }}" class="btn-primary btn-sm">الكل</a>
            </div>
            @forelse($myDebts as $debt)
                <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="font-weight:700;">{{ $debt->reference_number }}</div>
                    <div style="font-size:0.8rem;color:var(--muted);">المتبقي: {{ number_format($debt->remaining_balance, 2) }} ج.م</div>
                    <a href="{{ route('debts.show', $debt) }}" style="font-size:0.78rem;color:var(--primary);">عرض</a>
                </div>
            @empty
                <div style="padding:24px;text-align:center;color:var(--muted);">لا توجد ديون نشطة.</div>
            @endforelse
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-check"></i> أقساط قادمة</h3>
            </div>
            @forelse($nextInstallments as $inst)
                <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="font-weight:700;">قسط #{{ $inst->installment_number }}</div>
                    <div style="font-size:0.8rem;color:var(--muted);">الاستحقاق: {{ $inst->due_date->format('Y-m-d') }}</div>
                    <div style="font-weight:700;">{{ number_format($inst->amount, 2) }} ج.م</div>
                    <a href="{{ route('installments.pay', $inst) }}" style="font-size:0.78rem;color:var(--primary);">سداد</a>
                </div>
            @empty
                <div style="padding:24px;text-align:center;color:var(--muted);">لا توجد أقساط مستحقة.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
