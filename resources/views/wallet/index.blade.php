@extends('layouts.app')

@section('title', 'المحفظة')
@section('page-title', 'محفظتي الرقمية')
@section('page-subtitle', 'الرصيد وسجل طلبات الشحن')

@section('content')
<div class="page-content" style="padding:20px 32px;">
    <section class="statistics-section" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="stat-content">
                <h3>المتاح</h3>
                <p class="stat-number">{{ number_format($summary['available_balance'], 2) }} <small>ج.م</small></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <h3>المحجوز</h3>
                <p class="stat-number">{{ number_format($summary['reserved_balance'], 2) }} <small>ج.م</small></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <h3>طلبات معلّقة</h3>
                <p class="stat-number">{{ $summary['pending_topups'] }}</p>
            </div>
        </div>
    </section>

    <div style="margin:16px 0;">
        <a href="{{ route('wallet.topup') }}" class="btn-primary"><i class="fas fa-plus"></i> شحن المحفظة</a>
    </div>

    <div class="card">
        <div class="card-header"><h3>آخر الحركات</h3></div>
        @forelse($summary['recent_transactions'] as $tx)
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:0.88rem;">
                <span>{{ $tx->description }}</span>
                <span style="font-weight:700;">{{ number_format($tx->amount, 2) }} ج.م</span>
            </div>
        @empty
            <div style="padding:24px;text-align:center;color:var(--muted);">لا توجد حركات بعد.</div>
        @endforelse
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-header"><h3>طلبات الشحن</h3></div>
        {{ $topups->links() }}
        @forelse($topups as $t)
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:0.88rem;">
                <span>#{{ $t->id }} — {{ $t->payment_method_arabic }}</span>
                <span>{{ $t->status_arabic }} — {{ number_format($t->amount, 2) }} ج.م</span>
            </div>
        @empty
            <div style="padding:24px;text-align:center;color:var(--muted);">لا توجد طلبات شحن.</div>
        @endforelse
    </div>
</div>
@endsection
