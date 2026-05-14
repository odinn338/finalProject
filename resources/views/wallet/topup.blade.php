@extends('layouts.app')

@section('title', 'شحن المحفظة')
@section('page-title', 'شحن المحفظة')
@section('page-subtitle', 'فودافون كاش أو تحويل يدوي مع إيصال')

@section('content')
<div class="page-content" style="max-width:720px;margin:0 auto;padding:20px 32px;">
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><h3>الرصيد الحالي</h3></div>
        <p style="font-size:1.4rem;font-weight:900;">{{ number_format($wallet->available_balance, 2) }} ج.م</p>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><h3>فودافون كاش (Paymob)</h3></div>
        <form action="{{ route('wallet.topup.vodafone') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">المبلغ (ج.م)</label>
                <input type="number" name="amount" class="form-control" min="10" max="50000" step="0.01" required value="{{ old('amount') }}">
            </div>
            <div class="form-group">
                <label class="form-label">رقم فودافون كاش</label>
                <input type="text" name="wallet_phone" class="form-control" placeholder="01xxxxxxxxx" required value="{{ old('wallet_phone') }}">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">متابعة الدفع</button>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><h3>تحويل يدوي</h3></div>
        <form action="{{ route('wallet.topup.manual') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">المبلغ</label>
                <input type="number" name="amount" class="form-control" min="10" step="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">طريقة التحويل</label>
                <select name="payment_method" class="form-control" required>
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="cash_deposit">إيداع نقدي</option>
                    <option value="cheque">شيك</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">مرجع التحويل (اختياري)</label>
                <input type="text" name="transfer_reference" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">صورة الإيصال</label>
                <input type="file" name="receipt_image" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea name="user_notes" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">إرسال للمراجعة</button>
        </form>
    </div>
</div>
@endsection
