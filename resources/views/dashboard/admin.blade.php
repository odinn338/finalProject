@extends('layouts.app')

@section('title', 'لوحة تحكم المدير')
@section('page-title', 'لوحة تحكم المدير 🛡️')
@section('page-subtitle', 'نظرة شاملة على محفظة الديون ونشاط النظام')

@section('content')

<section class="statistics-section" style="grid-template-columns:repeat(4,1fr);">

    <div class="stat-card total-debt">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-content">
            <h3>إجمالي المستخدمين</h3>
            <p class="stat-number">{{ number_format($stats['total_users']) }}</p>
            <span class="stat-label">مستخدم مسجل</span>
        </div>
        <div class="stat-trend up"><i class="fas fa-user"></i></div>
    </div>

    <div class="stat-card paid">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-content">
            <h3>المحفظة الإجمالية</h3>
            <p class="stat-number" style="font-size:1.15rem;">{{ number_format($stats['total_portfolio'], 0) }} <small style="font-size:0.5em;">ج.م</small></p>
            <span class="stat-label">{{ $stats['active_debts'] }} دين نشط</span>
        </div>
        <div class="stat-trend" style="color:var(--success);"><i class="fas fa-chart-line"></i></div>
    </div>

    <div class="stat-card remaining">
        <div class="stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="stat-content">
            <h3>المحصَّل هذا الشهر</h3>
            <p class="stat-number" style="font-size:1.15rem;">{{ number_format($stats['monthly_collections'], 0) }} <small style="font-size:0.5em;">ج.م</small></p>
            <span class="stat-label">{{ now()->translatedFormat('F Y') }}</span>
        </div>
        <div class="stat-trend" style="color:var(--warning);"><i class="fas fa-calendar"></i></div>
    </div>

    <div class="stat-card overdue">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-content">
            <h3>طلبات تنتظر المراجعة</h3>
            <p class="stat-number">{{ $stats['pending_requests'] + $stats['pending_reschedule'] }}</p>
            <span class="stat-label">{{ $stats['pending_requests'] }} دين + {{ $stats['pending_reschedule'] }} جدولة</span>
        </div>
        <div class="stat-trend alert"><i class="fas fa-clock"></i><span>عاجل</span></div>
    </div>

</section>

{{-- ════════════ الرسم البياني + ملخص الديون ════════════ --}}
<section class="charts-section" style="padding:20px 32px 0;">

    <div class="chart-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="color:var(--primary);margin-left:8px;"></i>توزيع الديون</h3>
        </div>
        <div class="chart-container" style="height:200px;">
            <canvas id="debtStatusChart"></canvas>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px;margin-top:14px;">
            <div style="display:flex;justify-content:space-between;font-size:0.82rem;">
                <span style="color:var(--primary);">● نشط</span>
                <span style="color:var(--text);font-weight:700;">{{ $stats['active_debts'] }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.82rem;">
                <span style="color:var(--danger);">● متأخر</span>
                <span style="color:var(--text);font-weight:700;">{{ $stats['overdue_debts'] }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.82rem;">
                <span style="color:var(--success);">● مكتمل</span>
                <span style="color:var(--text);font-weight:700;">{{ $stats['completed_debts'] }}</span>
            </div>
        </div>
    </div>

    <div class="chart-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar" style="color:var(--primary);margin-left:8px;"></i>التحصيل مقابل الديون الجديدة (آخر 6 أشهر)</h3>
        </div>
        <div class="chart-container" style="height:200px;">
            <canvas id="adminMonthlyChart"></canvas>
        </div>
    </div>

</section>

{{-- ════════════ الطلبات المعلقة ════════════ --}}
<section style="padding:20px 32px 0;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- طلبات الديون المعلقة --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar" style="color:var(--warning);margin-left:8px;"></i>طلبات ديون معلقة</h3>
                <a href="{{ route('admin.requests.index') }}" class="btn-primary btn-sm">
                    <i class="fas fa-list"></i> عرض الكل
                </a>
            </div>
            @php $pendingReqs = \App\Models\DebtRequest::with('user')->where('status','pending')->latest()->limit(5)->get(); @endphp
            @forelse($pendingReqs as $req)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="width:36px;height:36px;border-radius:9px;background:rgba(243,156,18,0.15);display:flex;align-items:center;justify-content:center;color:var(--warning);flex-shrink:0;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:0.86rem;font-weight:700;color:var(--text);">{{ $req->user->name }}</div>
                        <div style="font-size:0.76rem;color:var(--muted);">{{ $req->title }}</div>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-size:0.88rem;font-weight:700;color:var(--text);">{{ number_format($req->requested_amount, 0) }} ج.م</div>
                        <a href="{{ route('admin.requests.show', $req->id) }}" style="font-size:0.74rem;color:var(--primary);font-weight:700;">مراجعة</a>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:24px;color:var(--muted);font-size:0.88rem;">
                    <i class="fas fa-check-circle" style="font-size:1.8rem;color:var(--success);display:block;margin-bottom:8px;opacity:0.7;"></i>
                    لا توجد طلبات معلقة
                </div>
            @endforelse
        </div>

        {{-- طلبات إعادة الجدولة المعلقة --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-sync-alt" style="color:var(--info);margin-left:8px;"></i>طلبات إعادة جدولة</h3>
                <a href="{{ route('admin.rescheduling.index') }}" class="btn-primary btn-sm">
                    <i class="fas fa-list"></i> عرض الكل
                </a>
            </div>
            @php $pendingReschedule = \App\Models\ReschedulingRequest::with('user','debt')->where('status','pending')->latest()->limit(5)->get(); @endphp
            @forelse($pendingReschedule as $rs)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="width:36px;height:36px;border-radius:9px;background:rgba(52,152,219,0.15);display:flex;align-items:center;justify-content:center;color:var(--info);flex-shrink:0;">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:0.86rem;font-weight:700;color:var(--text);">{{ $rs->user->name }}</div>
                        <div style="font-size:0.76rem;color:var(--muted);">{{ $rs->debt->reference_number }}</div>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-size:0.88rem;font-weight:700;color:var(--text);">{{ number_format($rs->outstanding_balance, 0) }} ج.م</div>
                        <a href="{{ route('admin.rescheduling.show', $rs->id) }}" style="font-size:0.74rem;color:var(--primary);font-weight:700;">مراجعة</a>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:24px;color:var(--muted);font-size:0.88rem;">
                    <i class="fas fa-check-circle" style="font-size:1.8rem;color:var(--success);display:block;margin-bottom:8px;opacity:0.7;"></i>
                    لا توجد طلبات معلقة
                </div>
            @endforelse
        </div>

    </div>
</section>

<div style="padding-bottom:32px;"></div>

@endsection

@push('scripts')
<script>
new Chart(document.getElementById('debtStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['نشط', 'متأخر', 'مكتمل'],
        datasets: [{
            data: [{{ $stats['active_debts'] }}, {{ $stats['overdue_debts'] }}, {{ $stats['completed_debts'] }}],
            backgroundColor: ['#6c63ff', '#e74c3c', '#2ecc71'],
            borderColor: ['#6c63ff', '#e74c3c', '#2ecc71'],
            borderWidth: 2,
        }],
    },
    options: {
        cutout: '65%', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
    },
});

new Chart(document.getElementById('adminMonthlyChart'), {
    type: 'bar',
    data: {
        labels:   {!! json_encode($chartData['labels']) !!},
        datasets: [
            {
                label: 'تحصيل',
                data: {!! json_encode($chartData['collections']) !!},
                backgroundColor: 'rgba(108,99,255,0.7)',
                borderRadius: 6,
            },
            {
                label: 'ديون جديدة',
                data: {!! json_encode($chartData['newDebts']) !!},
                backgroundColor: 'rgba(46,204,113,0.5)',
                borderRadius: 6,
            },
        ],
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#9ca3af', font: { family: 'Cairo', size: 11 } } }
        },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo', size: 10 } } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#9ca3af', font: { family: 'Cairo', size: 10 } } },
        },
    },
});
</script>
@endpush
