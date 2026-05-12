<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    /** قائمة ديون المستخدم */
    public function index()
    {
        $user  = Auth::user();
        $query = Debt::with('debtRequest')->latest();

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        } else {
            $query->with('user');
        }

        $debts = $query->paginate(10);
        return view('debts.index', compact('debts'));
    }

    /** تفاصيل دين واحد */
    public function show(Debt $debt)
    {
        if (!Auth::user()->isAdmin() && $debt->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بعرض هذا الدين.');
        }

        $debt->load('installments', 'debtRequest', 'reschedulingRequests', 'paymentLogs.recorder');

        $installments   = $debt->installments;
        $paymentLogs    = $debt->paymentLogs()->limit(10)->get();
        $canReschedule  = $debt->canBeRescheduled();

        return view('debts.show', compact('debt', 'installments', 'paymentLogs', 'canReschedule'));
    }
}
