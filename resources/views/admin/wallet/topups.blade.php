@extends('layouts.app')

@section('title', 'مراجعة الشحنات')
@section('page-title', 'طلبات شحن المحفظة')
@section('page-subtitle', 'المراجعة اليدوية والمكتملة')

@section('content')
<div class="page-content" style="padding:20px 32px;">
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header"><h3>قيد المراجعة</h3></div>
        @forelse($pending as $t)
            <div style="border-bottom:1px solid var(--border);padding:12px 0;">
                <div style="font-weight:700;">{{ $t->user?->name }} — {{ number_format($t->amount, 2) }} ج.م</div>
                <div style="font-size:0.8rem;color:var(--muted);">{{ $t->payment_method_arabic }}</div>
                <form action="{{ route('admin.wallet.topups.approve', $t) }}" method="POST" style="display:inline;margin-top:8px;">
                    @csrf
                    <button type="submit" class="btn-success btn-sm">موافقة</button>
                </form>
                <form action="{{ route('admin.wallet.topups.reject', $t) }}" method="POST" style="display:inline;margin-right:8px;">
                    @csrf
                    <input type="hidden" name="admin_notes" value="مرفوض من قائمة المراجعة السريعة لعدم اكتمال بيانات الإيصال.">
                    <button type="submit" class="btn-danger btn-sm">رفض</button>
                </form>
            </div>
        @empty
            <p style="padding:16px;color:var(--muted);">لا توجد طلبات معلّقة.</p>
        @endforelse
        {{ $pending->links() }}
    </div>

    <div class="card">
        <div class="card-header"><h3>سجل الطلبات</h3></div>
        @foreach($all as $t)
            <div style="border-bottom:1px solid var(--border);padding:10px 0;font-size:0.88rem;">
                #{{ $t->id }} — {{ $t->user?->name }} — {{ $t->status_arabic }} — {{ number_format($t->amount, 2) }} ج.م
            </div>
        @endforeach
        {{ $all->links() }}
    </div>
</div>
@endsection
