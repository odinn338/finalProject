@extends('layouts.app')

@section('title', 'طلباتي')
@section('page-title', 'طلبات القروض')
@section('page-subtitle', 'جميع الطلبات التي قدمتها')

@section('content')
<div class="page-content">

    {{-- زر طلب جديد --}}
    <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
        <a href="{{ route('debt-requests.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> طلب دين جديد
        </a>
    </div>

    @if($requests->isEmpty())
        <div style="text-align:center;padding:60px 20px;">
            <i class="fas fa-file-invoice-dollar"
               style="font-size:4rem;color:var(--muted);opacity:.3;display:block;margin-bottom:16px;"></i>
            <h3 style="color:var(--muted);margin-bottom:8px;">لا توجد طلبات بعد</h3>
            <p style="color:var(--muted);font-size:.88rem;margin-bottom:20px;">
                لم تقدم أي طلب قرض حتى الآن.
            </p>
            <a href="{{ route('debt-requests.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i> تقديم أول طلب
            </a>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-list" style="color:var(--primary);"></i>
                    &nbsp;طلباتي ({{ $requests->total() }})
                </h3>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان الطلب</th>
                            <th>المبلغ المطلوب</th>
                            <th>المدة</th>
                            <th>المبلغ المعتمد</th>
                            <th>الفائدة</th>
                            <th>تاريخ الطلب</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- $requests = paginator | $request = single item --}}
                        @foreach($requests as $request)
                            <tr>
                                <td style="color:var(--muted);font-size:.8rem;">{{ $request->id }}</td>

                                <td>
                                    <div style="font-weight:700;max-width:180px;
                                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $request->title }}
                                    </div>
                                    @if($request->description)
                                        <div style="font-size:.72rem;color:var(--muted);max-width:180px;
                                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $request->description }}
                                        </div>
                                    @endif
                                </td>

                                <td style="font-weight:700;color:var(--primary);">
                                    {{ number_format($request->requested_amount, 2) }} ج.م
                                </td>

                                <td>{{ $request->requested_months }} شهر</td>

                                <td>
                                    @if($request->isApproved() && $request->approved_amount)
                                        <span style="color:var(--success);font-weight:700;">
                                            {{ number_format($request->approved_amount, 2) }} ج.م
                                        </span>
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if($request->interest_rate)
                                        <span style="color:var(--warning);font-weight:700;">
                                            {{ $request->interest_rate }}%
                                        </span>
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>

                                <td style="font-size:.82rem;color:var(--muted);">
                                    {{ $request->created_at->format('Y-m-d') }}
                                </td>

                                <td>
                                    <span class="badge badge-{{ $request->status_color }}">
                                        {{ $request->status_arabic }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('debt-requests.show', $request->id) }}"
                                       class="btn-secondary btn-sm">
                                        <i class="fas fa-eye"></i> تفاصيل
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding:16px;">
                {{ $requests->links() }}
            </div>
        </div>
    @endif

</div>
@endsection
