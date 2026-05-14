@extends('layouts.app')

@section('title', 'طلبات إعادة الجدولة')
@section('page-title', 'طلبات إعادة الجدولة')
@section('page-subtitle', 'مراجعة طلبات إعادة جدولة الديون المقدمة من المستخدمين')

@section('content')
<div class="page-content">

    {{-- ════ فلتر الحالة ════ --}}
    <div style="display:flex;gap:12px;margin-bottom:20px;align-items:center;flex-wrap:wrap;">
        @php
            $tabs = [
                'pending'  => ['label'=>'قيد المراجعة','color'=>'warning'],
                'approved' => ['label'=>'موافق عليه',  'color'=>'success'],
                'rejected' => ['label'=>'مرفوض',       'color'=>'danger'],
            ];
        @endphp

        <a href="{{ route('admin.rescheduling.index') }}"
           style="padding:7px 16px;border-radius:8px;font-size:.82rem;font-weight:700;
                  text-decoration:none;border:1px solid var(--border);
                  {{ !request('status') ? 'background:rgba(108,99,255,.2);color:var(--primary-light);border-color:var(--primary);' : 'color:var(--muted);' }}">
            الكل ({{ \App\Models\ReschedulingRequest::count() }})
        </a>

        @foreach($tabs as $key => $meta)
            @php $cnt = \App\Models\ReschedulingRequest::where('status',$key)->count(); @endphp
            <a href="{{ route('admin.rescheduling.index', ['status'=>$key]) }}"
               style="padding:7px 16px;border-radius:8px;font-size:.82rem;font-weight:700;
                      text-decoration:none;border:1px solid var(--border);display:flex;align-items:center;gap:6px;
                      {{ request('status')===$key ? 'background:rgba(108,99,255,.2);color:var(--primary-light);border-color:var(--primary);' : 'color:var(--muted);' }}">
                <span class="badge badge-{{ $meta['color'] }}" style="font-size:.68rem;">{{ $cnt }}</span>
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- ════ جدول الطلبات ════ --}}
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-sync-alt" style="color:var(--info);"></i>
                &nbsp;طلبات إعادة الجدولة
                <span style="font-size:.78rem;color:var(--muted);font-weight:400;margin-right:6px;">
                    ({{ $requests->total() }} طلب)
                </span>
            </h3>
        </div>

        @if($requests->isEmpty())
            <div style="text-align:center;padding:50px 20px;color:var(--muted);">
                <i class="fas fa-check-circle"
                   style="font-size:3rem;color:var(--success);opacity:.4;display:block;margin-bottom:12px;"></i>
                لا توجد طلبات إعادة جدولة حالياً
            </div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>رقم الدين</th>
                            <th>الرصيد غير المسدد</th>
                            <th>الأقساط المتبقية</th>
                            <th>تاريخ الطلب</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $reschedule)
                            <tr>
                                <td style="color:var(--muted);font-size:.8rem;">{{ $reschedule->id }}</td>

                                <td>
                                    <div style="font-weight:700;">
                                        {{ $reschedule->user?->name ?? '—' }}
                                    </div>
                                    <div style="font-size:.72rem;color:var(--muted);">
                                        {{ $reschedule->user?->email ?? '' }}
                                    </div>
                                </td>

                                <td>
                                    <span style="font-weight:700;color:var(--primary);">
                                        {{ $reschedule->debt?->reference_number ?? '—' }}
                                    </span>
                                </td>

                                <td style="font-weight:700;color:var(--danger);">
                                    {{ number_format($reschedule->outstanding_balance, 2) }} ج.م
                                </td>

                                <td>{{ $reschedule->remaining_installments }} قسط</td>

                                <td style="font-size:.82rem;color:var(--muted);">
                                    {{ $reschedule->created_at->format('Y-m-d') }}
                                    <div style="font-size:.7rem;">{{ $reschedule->created_at->diffForHumans() }}</div>
                                </td>

                                <td>
                                    <span class="badge badge-{{ $reschedule->status_arabic === 'قيد المراجعة' ? 'warning' : ($reschedule->isApproved() ? 'success' : 'danger') }}">
                                        {{ $reschedule->status_arabic }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.rescheduling.show', $reschedule->id) }}"
                                       class="btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> مراجعة
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding:16px;">
                {{ $requests->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
