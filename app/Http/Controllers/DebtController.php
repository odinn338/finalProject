<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    /** قائمة ديون المستخدم */
    public function index()
    {
        $user = Auth::user();
        $query = Debt::with('debtRequest')->latest();

        if ($user->isAdmin()) {
            $query->with(['lender', 'borrower']);
        } elseif ($user->isCreditor()) {
            $query->where('lender_id', $user->id)->with('borrower');
        } else {
            $query->where(function ($q) use ($user): void {
                $q->where('debtor_id', $user->id)
                    ->orWhere(function ($q2) use ($user): void {
                        $q2->whereNull('debtor_id')->where('user_id', $user->id);
                    });
            });
        }

        $debts = $query->paginate(10);

        return view('debts.index', compact('debts'));
    }

    /** تفاصيل دين واحد */
    public function show(Debt $debt)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // مسموح
        } elseif ($user->isCreditor() && (int) $debt->lender_id === (int) $user->id) {
            // مسموح
        } elseif ($user->isDebtor() && (
            (int) $debt->debtor_id === (int) $user->id
            || ((int) $debt->user_id === (int) $user->id && $debt->debtor_id === null)
        )) {
            // مسموح
        } else {
            abort(403, 'غير مصرح لك بعرض هذا الدين.');
        }

        $debt->load('installments', 'debtRequest', 'reschedulingRequests', 'paymentLogs.recorder', 'lender', 'borrower');

        $installments = $debt->installments;
        $paymentLogs = $debt->paymentLogs()->limit(10)->get();
        $canReschedule = $debt->canBeRescheduled();

        return view('debts.show', compact('debt', 'installments', 'paymentLogs', 'canReschedule'));
    }
}
