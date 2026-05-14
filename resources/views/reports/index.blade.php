@extends('layouts.app')

@section('title', 'التقارير والتصدير')
@section('page-title', 'التقارير والتصدير')
@section('page-subtitle', 'تصدير البيانات إلى PDF أو Excel')

@section('content')
<div class="page-content">

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;max-width:900px;">

        {{-- تصدير ملخص ديوني - PDF --}}
        <div class="card" style="text-align:center;border-color:rgba(231,76,60,0.3);">
            <div style="width:64px;height:64px;background:rgba(231,76,60,0.15);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.8rem;color:var(--danger);">
                <i class="fas fa-file-pdf"></i>
            </div>
            <h3 style="font-size:1rem;margin-bottom:8px;">ملخص ديوني</h3>
            <p style="font-size:0.82rem;color:var(--muted);margin-bottom:20px;line-height:1.5;">
                تصدير ملخص شامل لجميع ديونك وجداول الأقساط بصيغة PDF جاهز للطباعة.
            </p>
            <a href="{{ route('reports.debt.pdf') }}" class="btn-danger" style="width:100%;justify-content:center;">
                <i class="fas fa-download"></i> تصدير PDF
            </a>
        </div>

        {{-- تصدير سجل الدفعات - Excel --}}
        <div class="card" style="text-align:center;border-color:rgba(46,204,113,0.3);">
            <div style="width:64px;height:64px;background:rgba(46,204,113,0.15);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.8rem;color:var(--success);">
                <i class="fas fa-file-excel"></i>
            </div>
            <h3 style="font-size:1rem;margin-bottom:8px;">سجل الدفعات</h3>
            <p style="font-size:0.82rem;color:var(--muted);margin-bottom:20px;line-height:1.5;">
                تصدير كل سجلات الدفع بصيغة CSV متوافقة مع Excel بدعم كامل للعربية.
            </p>
            <form action="{{ route('reports.payments.excel') }}" method="GET" style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;gap:8px;">
                    <div style="flex:1;">
                        <input type="date" name="from" class="form-control" style="font-size:0.8rem;padding:8px;" placeholder="من تاريخ">
                    </div>
                    <div style="flex:1;">
                        <input type="date" name="to" class="form-control" style="font-size:0.8rem;padding:8px;" placeholder="إلى تاريخ">
                    </div>
                </div>
                <button type="submit" class="btn-success" style="width:100%;justify-content:center;">
                    <i class="fas fa-download"></i> تصدير Excel
                </button>
            </form>
        </div>

        @if(auth()->user()->isAdmin())
        {{-- ملخص تنفيذي للمدير --}}
        <div class="card" style="text-align:center;border-color:rgba(108,99,255,0.3);">
            <div style="width:64px;height:64px;background:rgba(108,99,255,0.15);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.8rem;color:var(--primary);">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3 style="font-size:1rem;margin-bottom:8px;">ملخص تنفيذي</h3>
            <p style="font-size:0.82rem;color:var(--muted);margin-bottom:20px;line-height:1.5;">
                تقرير إداري شامل عن المحفظة الكاملة وأكبر المديونين بصيغة PDF.
            </p>
            <a href="{{ route('admin.reports.admin.pdf') }}" class="btn-primary" style="width:100%;justify-content:center;">
                {{-- route name: admin.reports.admin.pdf → web.php line 80 --}}
                <i class="fas fa-download"></i> تصدير PDF
            </a>
        </div>
        @endif

    </div>

</div>
@endsection
