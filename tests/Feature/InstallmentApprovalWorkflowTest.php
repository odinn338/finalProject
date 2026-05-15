<?php

use App\Models\Debt;
use App\Models\DebtRequest;
use App\Models\Installment;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->lender = User::factory()->creditor()->create();
    $this->debtor = User::factory()->debtor()->create();
    $this->admin = User::factory()->admin()->create();

    $debtRequest = DebtRequest::factory()->create(['user_id' => $this->debtor->id]);

    $this->debt = Debt::factory()->create([
        'debt_request_id' => $debtRequest->id,
        'user_id' => $this->debtor->id,
        'debtor_id' => $this->debtor->id,
        'lender_id' => $this->lender->id,
    ]);

    $this->installment = Installment::factory()->create([
        'debt_id' => $this->debt->id,
        'user_id' => $this->debtor->id,
        'status' => Installment::STATUS_PENDING,
        'amount' => 500,
        'paid_amount' => 0,
    ]);
});

it('debtor can submit a payment request without moving wallet funds', function () {
    $response = $this->actingAs($this->debtor)
        ->post(route('installments.pay.post', ['installment' => $this->installment->id]), [
            'reference_number' => 'REF-001',
            'notes' => 'حوالة بنكية',
        ]);

    $response->assertRedirect();
    $this->installment->refresh();
    expect($this->installment->status)->toBe(Installment::STATUS_PENDING_APPROVAL);
    expect($this->installment->payment_reference)->toBe('REF-001');
    expect((float) $this->installment->paid_amount)->toBe(0.0);
});

it('lender cannot submit a payment request for someone else installment', function () {
    $this->actingAs($this->lender)
        ->post(route('installments.pay.post', ['installment' => $this->installment->id]), [
            'reference_number' => 'REF-002',
        ])
        ->assertForbidden();
});

it('pay action is blocked when status is pending_approval', function () {
    $this->installment->update(['status' => Installment::STATUS_PENDING_APPROVAL]);

    expect($this->installment->isPendingApproval())->toBeTrue();

    $this->actingAs($this->debtor)
        ->post(route('installments.pay.post', ['installment' => $this->installment->id]), [
            'reference_number' => 'REF-003',
        ])
        ->assertStatus(422);
});

it('admin can access pending installments dashboard', function () {
    $this->installment->update(['status' => Installment::STATUS_PENDING_APPROVAL]);

    $this->actingAs($this->admin)
        ->get(route('admin.installments.pending'))
        ->assertSuccessful()
        ->assertSee('بانتظار التأكيد');
});

it('admin can approve a pending_approval installment via debt service', function () {
    $this->installment->update([
        'status' => Installment::STATUS_PENDING_APPROVAL,
        'payment_reference' => 'REF-003',
    ]);

    $this->mock(DebtService::class)
        ->shouldReceive('approveInstallmentPayment')
        ->once()
        ->with(Mockery::on(fn ($i) => $i->id === $this->installment->id), $this->admin->id);

    $this->actingAs($this->admin)
        ->post(route('admin.installments.approve', ['installment' => $this->installment->id]))
        ->assertRedirect();
});

it('non-admin cannot access admin approve route', function () {
    $this->installment->update(['status' => Installment::STATUS_PENDING_APPROVAL]);

    $this->actingAs($this->debtor)
        ->post(route('admin.installments.approve', ['installment' => $this->installment->id]))
        ->assertForbidden();
});

it('user isAdmin helper uses role column', function () {
    expect($this->admin->isAdmin())->toBeTrue();
    expect($this->debtor->isAdmin())->toBeFalse();
    expect($this->admin->role)->toBe('admin');
});

it('status_arabic returns correct Arabic label for pending_approval', function () {
    $this->installment->status = Installment::STATUS_PENDING_APPROVAL;
    expect($this->installment->status_arabic)->toBe('بانتظار تأكيد الإدارة');
});

it('status_color returns info for pending_approval', function () {
    $this->installment->status = Installment::STATUS_PENDING_APPROVAL;
    expect($this->installment->status_color)->toBe('info');
});
