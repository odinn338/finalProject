@extends('layouts.app')

@section('title', 'طلبات الديون')
@section('page-title', 'طلبات الديون')
@section('page-subtitle', 'مراجعة وإدارة جميع طلبات الديون المقدمة')

@section('content')
<div class="page-content">

    {{-- ════════ فلاتر البحث ════════ --}}
    <div class="card" style="margin-bottom:20px;">
        <form method="GET" action="{{ route('admin.requests.index') }}"
              style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">

            <div style="flex:1;min-width:200px;">
                <label class="form-label">بحث بالاسم / البريد</label>
                <div style="position:relative;">
                    <i class="fas fa-search"
                       style="position:absolute;right:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem;"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" style="padding-right:38px;"
                           placeholder="اسم المستخدم أو بريده...">
                </div>
            </div>

            <div style="min-width:180px;">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-control">
                    <option value="">-- جميع الحالات --</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>قيد المراجعة</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>موافق عليه</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                    <option value="cancelled"{{ request('status') === 'cancelled'? 'selected' : '' }}>ملغي</option>
                </select>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-filter"></i> فلترة
                </button>
                <a href="{{ route('admin.requests.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> إعادة ضبط
                </a>
            </div>
        </form>
    </div>

    {{-- ════════ إحصائيات سريعة ════════ --}}
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        @php
            $allStatuses = [
                'pending'  => ['label'=>'قيد المراجعة', 'color'=>'warning'],
                'approved' => ['label'=>'موافق عليه',   'color'=>'success'],
                'rejected' => ['label'=>'مرفوض',        'color'=>'danger'],
            ];
        @endphp
        @foreach($allStatuses as $key => $meta)
            <a href="{{ route('admin.requests.index', ['status'=>$key]) }}"
               style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;
                      background:rgba(255,255,255,0.05);border:1px solid var(--border);
                      border-radius:8px;font-size:.82rem;font-weight:700;
                      color:{{ request('status')===$key ? 'white' : 'var(--muted)' }};
                      {{ request('status')===$key ? 'background:rgba(108,99,255,.2);border-color:var(--primary);' : '' }}
                      text-decoration:none;transition:.2s;">
                <span class="badge badge-{{ $meta['color'] }}" style="font-size:.7rem;">
                    {{ $statusCounts[$key] ?? 0 }}
                </span>
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- ════════ جدول الطلبات ════════ --}}
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-file-invoice-dollar" style="color:var(--primary);"></i>
                &nbsp;الطلبات
                <span style="font-size:.78rem;color:var(--muted);font-weight:400;margin-right:6px;">
                    ({{ $requests->total() }} طلب)
                </span>
            </h3>
        </div>

        @if($requests->isEmpty())
            <div style="text-align:center;padding:50px 20px;color:var(--muted);">
                <i class="fas fa-inbox" style="font-size:3rem;opacity:.3;display:block;margin-bottom:12px;"></i>
                لا توجد طلبات تطابق معايير البحث
            </div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المستخدم</th>
                            <th>عنوان الطلب</th>
                            <th>المبلغ المطلوب</th>
                            <th>المدة</th>
                            <th>تاريخ الطلب</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- $requests هو paginator، $request هو عنصر واحد داخل الحلقة --}}
                        @foreach($requests as $request)
                            <tr>
                                <td style="color:var(--muted);font-size:.8rem;">{{ $request->id }}</td>

                                <td>
                                    <div style="font-weight:700;font-size:.9rem;">
                                        {{ $request->user?->name ?? '—' }}
                                    </div>
                                    <div style="font-size:.72rem;color:var(--muted);">
                                        {{ $request->user?->email ?? '' }}
                                    </div>
                                </td>

                                <td>
                                    <div style="font-weight:600;max-width:200px;
                                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $request->title }}
                                    </div>
                                </td>

                                <td style="font-weight:700;color:var(--primary);">
                                    {{ number_format($request->requested_amount, 2) }} ج.م
                                </td>

                                <td>{{ $request->requested_months }} شهر</td>

                                <td style="font-size:.82rem;color:var(--muted);">
                                    {{ $request->created_at->format('Y-m-d') }}
                                    <div style="font-size:.7rem;">
                                        {{ $request->created_at->diffForHumans() }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge badge-{{ $request->status_color }}">
                                        {{ $request->status_arabic }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('admin.requests.show', $request->id) }}"
                                       class="btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> مراجعة
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div style="padding:16px;">
                {{ $requests->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
