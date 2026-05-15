@extends('layouts.app')

@section('title', 'تفاصيل الدين - ' . $debt->reference_number)
@section('page-title', 'تفاصيل الدين')
@section('page-subtitle', 'رقم الدين: ' . $debt->reference_number)

@section('content')
<div class="page-content">

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

        {{-- الجانب الرئيسي --}}
        <div>

            {{-- بطاقة ملخص الدين --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie" style="color:var(--primary);"></i> &nbsp;ملخص الدين</h3>
                    <span class="badge badge-{{ $debt->status_color }}">{{ $debt->status_arabic }}</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;">
                    <div style="text-align:center;padding:14px;background:rgba(108,99,255,0.07);border-radius:10px;">
                        <div style="font-size:0.72rem;color:var(--muted);margin-bottom:4px;">المبلغ الأصلي</div>
                        <div style="font-weight:900;font-size:1.05rem;color:var(--text);">{{ number_format($debt->principal_amount, 2) }}</div>
                        <div style="font-size:0.68rem;color:var(--muted);">ج.م</div>
                    </div>
                    <div style="text-align:center;padding:14px;background:rgba(243,156,18,0.07);border-radius:10px;">
                        <div style="font-size:0.72rem;color:var(--muted);margin-bottom:4px;">الفائدة ({{ $debt->interest_rate }}%)</div>
                        <div style="font-weight:900;font-size:1.05rem;color:var(--warning);">{{ number_format($debt->interest_amount, 2) }}</div>
                        <div style="font-size:0.68rem;color:var(--muted);">ج.م</div>
                    </div>
                    <div style="text-align:center;padding:14px;background:rgba(46,204,113,0.07);border-radius:10px;">
                        <div style="font-size:0.72rem;color:var(--muted);margin-bottom:4px;">المسدد</div>
                        <div style="font-weight:900;font-size:1.05rem;color:var(--success);">{{ number_format($debt->total_paid, 2) }}</div>
                        <div style="font-size:0.68rem;color:var(--muted);">ج.م</div>
                    </div>
                    <div style="text-align:center;padding:14px;background:rgba(231,76,60,0.07);border-radius:10px;">
                        <div style="font-size:0.72rem;color:var(--muted);margin-bottom:4px;">المتبقي</div>
                        <div style="font-weight:900;font-size:1.05rem;color:var(--danger);">{{ number_format($debt->remaining_balance, 2) }}</div>
                        <div style="font-size:0.68rem;color:var(--muted);">ج.م</div>
                    </div>
                </div>

                {{-- شريط التقدم --}}
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.8rem;color:var(--muted);margin-bottom:8px;">
                        <span>تقدم السداد</span>
                        <span>{{ $debt->progress_percentage }}% مكتمل</span>
                    </div>
                    <div class="progress" style="height:10px;">
                        <div class="progress-bar {{ $debt->progress_percentage >= 100 ? 'success' : '' }}"
                             style="width:{{ $debt->progress_percentage }}%;"></div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
                    <div>
                        <div style="font-size:0.72rem;color:var(--muted);">تاريخ البدء</div>
                        <div style="font-weight:700;font-size:0.88rem;">{{ $debt->start_date->format('Y-m-d') }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:var(--muted);">تاريخ الانتهاء</div>
                        <div style="font-weight:700;font-size:0.88rem;">{{ $debt->end_date->format('Y-m-d') }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:var(--muted);">القسط الشهري</div>
                        <div style="font-weight:900;font-size:1rem;color:var(--primary);">{{ number_format($debt->monthly_installment, 2) }} ج.م</div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:var(--muted);">إجمالي الأشهر</div>
                        <div style="font-weight:700;font-size:0.88rem;">{{ $debt->total_months }} شهر</div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:var(--muted);">المسدد منها</div>
                        <div style="font-weight:700;font-size:0.88rem;color:var(--success);">{{ $debt->paid_months }} شهر</div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:var(--muted);">الإجمالي</div>
                        <div style="font-weight:900;color:var(--text);">{{ number_format($debt->total_amount, 2) }} ج.م</div>
                    </div>
                </div>
            </div>

            {{-- جدول الأقساط --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-table" style="color:var(--primary);"></i> &nbsp;جدول الأقساط</h3>
                    <span style="font-size:0.8rem;color:var(--muted);">{{ $installments->count() }} قسط</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>تاريخ الاستحقاق</th>
                                <th>القيمة</th>
                                <th>المسدد</th>
                                <th>غرامة</th>
                                <th>الحالة</th>
                                <th>تاريخ السداد</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($installments as $inst)
                                <tr style="
                                    @if($inst->status === 'paid') background:rgba(46,204,113,0.05);
                                    @elseif($inst->status === 'overdue') background:rgba(231,76,60,0.05);
                                    @elseif($inst->status === 'voided') opacity:0.5;
                                    @endif
                                ">
                                    <td style="font-weight:700;color:var(--primary);">{{ $inst->installment_number }}</td>
                                    <td>{{ $inst->due_date->format('Y-m-d') }}</td>
                                    <td style="font-weight:700;">{{ number_format($inst->amount, 2) }}</td>
                                    <td style="color:var(--success);">{{ number_format($inst->paid_amount, 2) }}</td>
                                    <td style="color:{{ $inst->penalty_amount > 0 ? 'var(--danger)' : 'var(--muted)' }};">
                                        {{ number_format($inst->penalty_amount, 2) }}
                                    </td>
                                    <td><span class="badge badge-{{ $inst->status_color }}">{{ $inst->status_arabic }}</span></td>
                                    <td>{{ $inst->paid_date ? $inst->paid_date->format('Y-m-d') : '-' }}</td>
{{-- بعد --}}
<td>
    @if(in_array($inst->status, ['pending','overdue','partial']))
        <a href="{{ route('installments.pay', $inst->id) }}" class="btn-success btn-sm">
            <i class="fas fa-money-bill-wave"></i> سداد
        </a>

    @elseif($inst->status === 'pending_approval')
        {{-- زرار تأكيد الدفع للأدمن فقط --}}
        @if(auth()->user()->is_admin)
            <form method="POST"
                  action="{{route('admin.installments.approve', [$inst->id]) }}"
                  onsubmit="return confirm('تأكيد الدفع وتحويل المبلغ؟')"
                  style="display:inline;">
                @csrf
                <button type="submit" class="btn-success btn-sm">
                    <i class="fas fa-check-circle"></i> تأكيد الدفع
                </button>
            </form>
        @else
            <span style="color:var(--info,#17a2b8);font-size:0.8rem;">
                <i class="fas fa-clock"></i> بانتظار الأدمن
            </span>
        @endif

    @elseif($inst->status === 'paid')
        <span style="color:var(--success);font-size:0.8rem;">
            <i class="fas fa-check-circle"></i> مسدد
        </span>

    @else
        <span style="color:var(--muted);font-size:0.8rem;">-</span>
    @endif
</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- الجانب الجانبي --}}
        <div>

            {{-- الإجراءات --}}
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header">
                    <h3><i class="fas fa-cog" style="color:var(--primary);"></i> &nbsp;الإجراءات</h3>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <a href="{{ route('reports.debt.pdf', ['user_id' => $debt->debtor_id ?? $debt->user_id]) }}" class="btn-danger" style="justify-content:center;">
                        <i class="fas fa-file-pdf"></i> تصدير PDF
                    </a>
                    @if($canReschedule)
                        <a href="{{ route('rescheduling.create', $debt->id) }}" class="btn-warning" style="justify-content:center;">
                            <i class="fas fa-sync-alt"></i> طلب إعادة جدولة
                        </a>
                    @endif
                    @if($debt->reschedulingRequests->where('status','pending')->count() > 0)
                        <div class="alert alert-info" style="font-size:0.82rem;margin:0;">
                            <i class="fas fa-clock"></i> طلب إعادة جدولة قيد المراجعة
                        </div>
                    @endif
                </div>
            </div>

            {{-- آخر المدفوعات --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-history" style="color:var(--primary);"></i> &nbsp;سجل الدفعات</h3>
                </div>
                @forelse($paymentLogs as $log)
                    <div style="padding:10px 0;border-bottom:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                            <span style="font-size:0.82rem;font-weight:700;color:var(--success);">
                                + {{ number_format($log->amount_paid, 2) }} ج.م
                            </span>
                            <span style="font-size:0.72rem;color:var(--muted);">{{ $log->payment_date->format('Y-m-d') }}</span>
                        </div>
                        <div style="font-size:0.72rem;color:var(--muted);">
                            {{ $log->payment_method_arabic }}
                            @if($log->reference_number) &nbsp;|&nbsp; {{ $log->reference_number }} @endif
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:20px;color:var(--muted);font-size:0.85rem;">
                        لا توجد دفعات مسجلة بعد
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <div style="margin-top:16px;">
        <a href="{{ route('debts.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-right"></i> العودة للقائمة
        </a>
    </div>

</div>
@endsection
