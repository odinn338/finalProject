@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'مرحباً، ' . auth()->user()->name . '! 👋')
@section('page-subtitle', 'إليك نظرة سريعة على وضعك المالي اليوم')

@section('content')

{{-- ════════════ بطاقات الإحصائيات ════════════ --}}
<section class="statistics-section">

    <div class="stat-card total-debt">
        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
        <div class="stat-content">
            <h3>إجمالي الديون</h3>
            <p class="stat-number">{{ number_format($stats['total_debt'], 2) }} <small style="font-size:0.55em;font-weight:600;">ج.م</small></p>
            <span class="stat-label">جميع الديون المسجلة</span>
        </div>
        <div class="stat-trend up"><i class="fas fa-file-invoice-dollar"></i><span>{{ $stats['active_debts'] }} نشط</span></div>
    </div>

    <div class="stat-card paid">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-content">
            <h3>المبالغ المسددة</h3>
            <p class="stat-number">{{ number_format($stats['total_paid'], 2) }} <small style="font-size:0.55em;font-weight:600;">ج.م</small></p>
            <span class="stat-label">تم سدادها بنجاح</span>
        </div>
        <div class="stat-trend" style="color: var(--success);">
            <i class="fas fa-check"></i>
            <span>
                @if($stats['total_debt'] > 0)
                    {{ round(($stats['total_paid'] / $stats['total_debt']) * 100, 1) }}%
                @else 0% @endif
            </span>
        </div>
    </div>

    <div class="stat-card remaining">
        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-content">
            <h3>المتبقي للسداد</h3>
            <p class="stat-number">{{ number_format($stats['remaining'], 2) }} <small style="font-size:0.55em;font-weight:600;">ج.م</small></p>
            <span class="stat-label">يجب سداده</span>
        </div>
        <div class="stat-trend down"><i class="fas fa-arrow-down"></i><span>متبقي</span></div>
    </div>

    <div class="stat-card overdue">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-content">
            <h3>متأخر السداد</h3>
            <p class="stat-number">{{ number_format($stats['overdue_amount'], 2) }} <small style="font-size:0.55em;font-weight:600;">ج.م</small></p>
            <span class="stat-label">يحتاج انتباهاً فورياً</span>
        </div>
        <div class="stat-trend alert"><i class="fas fa-clock"></i><span>عاجل</span></div>
    </div>

</section>

{{-- ════════════ الرسوم البيانية ════════════ --}}
<section class="charts-section">

    {{-- دائرة التقدم --}}
    <div class="chart-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="color:var(--primary);margin-left:8px;"></i>نسبة السداد</h3>
        </div>
        <div class="chart-container" style="height:220px;">
            <canvas id="progressChart"></canvas>
            <div class="chart-center-text">
                <h2>{{ $stats['total_debt'] > 0 ? round(($stats['total_paid'] / $stats['total_debt']) * 100, 1) : 0 }}%</h2>
                <p>مكتمل</p>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;margin-top:16px;">
            <div style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--muted);">
                <span style="width:12px;height:12px;border-radius:50%;background:var(--primary);flex-shrink:0;"></span>
                مسدد ({{ number_format($pieData['paid'], 2) }} ج.م)
            </div>
            <div style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--muted);">
                <span style="width:12px;height:12px;border-radius:50%;background:rgba(255,255,255,0.12);flex-shrink:0;"></span>
                متبقي ({{ number_format($pieData['remaining'], 2) }} ج.م)
            </div>
        </div>
    </div>

    {{-- الرسم الشهري --}}
    <div class="chart-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line" style="color:var(--primary);margin-left:8px;"></i>تحليل السداد الشهري</h3>
        </div>
        <div class="chart-container" style="height:220px;">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

</section>

{{-- ════════════ آخر المعاملات + القسط القادم ════════════ --}}
<section style="display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:20px 32px 0;">

    {{-- آخر المعاملات --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history" style="color:var(--primary);margin-left:8px;"></i>آخر الدفعات</h3>
            <a href="{{ route('reports.index') }}" style="font-size:0.82rem;color:var(--primary);">عرض الكل <i class="fas fa-arrow-left"></i></a>
        </div>
        @forelse($recentPayments as $pay)
            <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(46,204,113,0.15);display:flex;align-items:center;justify-content:center;color:var(--success);flex-shrink:0;">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:0.88rem;font-weight:700;color:var(--text);">
                        {{ $pay->debt->reference_number ?? 'دفعة' }}
                    </div>
                    <div style="font-size:0.78rem;color:var(--muted);">
                        {{ $pay->payment_date->diffForHumans() }}
                    </div>
                </div>
                <div style="font-size:0.92rem;font-weight:700;color:var(--success);">
                    - {{ number_format($pay->amount_paid, 2) }} ج.م
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:30px;color:var(--muted);">
                <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:10px;display:block;opacity:0.4;"></i>
                لا توجد دفعات مسجلة بعد
            </div>
        @endforelse
    </div>

    {{-- الأقساط القادمة --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-calendar-alt" style="color:var(--warning);margin-left:8px;"></i>الأقساط القادمة</h3>
            <a href="{{ route('debts.index') }}" style="font-size:0.82rem;color:var(--primary);">عرض الكل <i class="fas fa-arrow-left"></i></a>
        </div>
        @forelse($upcomingInstallments as $inst)
            @php
                $isOverdue = $inst->status === 'overdue';
                $daysLeft  = $isOverdue ? 0 : now()->diffInDays($inst->due_date, false);
                $color     = $isOverdue ? 'var(--danger)' : ($daysLeft <= 3 ? 'var(--warning)' : 'var(--text-muted)');
            @endphp
            <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="width:40px;height:40px;border-radius:10px;background:rgba(108,99,255,0.12);display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:0.88rem;font-weight:700;color:var(--text);">
                        قسط #{{ $inst->installment_number }} - {{ $inst->debt->reference_number ?? '' }}
                    </div>
                    <div style="font-size:0.78rem;color:{{ $color }};">
                        @if($isOverdue)
                            <i class="fas fa-exclamation-circle"></i> متأخر {{ $inst->days_overdue }} يوم
                        @else
                            استحقاق: {{ $inst->due_date->format('Y-m-d') }}
                        @endif
                    </div>
                </div>
                <div style="text-align:left;">
                    <div style="font-size:0.92rem;font-weight:700;color:var(--text);">{{ number_format($inst->amount, 2) }} ج.م</div>
                    <a href="{{ route('installments.pay', $inst->id) }}" style="font-size:0.75rem;color:var(--primary);font-weight:700;">سداد الآن</a>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:30px;color:var(--muted);">
                <i class="fas fa-check-double" style="font-size:2rem;margin-bottom:10px;display:block;opacity:0.4;color:var(--success);"></i>
                لا توجد أقساط مستحقة 🎉
            </div>
        @endforelse
    </div>

</section>

{{-- طلب معلق --}}
@if($stats['pending_requests'] > 0)
<div style="padding:20px 32px;">
    <div class="alert alert-info" style="border-radius:12px;">
        <i class="fas fa-clock"></i>
        لديك <strong>{{ $stats['pending_requests'] }}</strong> طلب(ات) قيد المراجعة من الإدارة.
        <a href="{{ route('debt-requests.index') }}" style="color:inherit;font-weight:700;text-decoration:underline;margin-right:8px;">عرض الطلبات</a>
    </div>
</div>
@endif

<div style="padding-bottom:32px;"></div>

@endsection

@push('scripts')
<script>
// ── رسم الدائرة ──────────────────────────────
const paid      = {{ $pieData['paid'] }};
const remaining = {{ $pieData['remaining'] }};

new Chart(document.getElementById('progressChart'), {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [paid, remaining],
            backgroundColor: ['#6c63ff', 'rgba(255,255,255,0.08)'],
            borderColor:     ['#6c63ff', 'rgba(255,255,255,0.05)'],
            borderWidth: 2,
            hoverOffset: 6,
        }],
    },
    options: {
        cutout: '72%',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        animation: { animateRotate: true, duration: 1200 },
    },
});

// ── الرسم الشهري ─────────────────────────────
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels:   {!! json_encode($monthlyData['labels']) !!},
        datasets: [{
            label:           'المدفوع (ج.م)',
            data:            {!! json_encode($monthlyData['data']) !!},
            borderColor:     '#6c63ff',
            backgroundColor: 'rgba(108,99,255,0.12)',
            borderWidth:     2.5,
            pointBackgroundColor: '#6c63ff',
            pointRadius:     5,
            pointHoverRadius:7,
            tension:         0.4,
            fill:            true,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo', size: 11 } } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo', size: 11 } } },
        },
    },
});
</script>
@endpush
