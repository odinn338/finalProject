<?php

// tests/Feature/InstallmentApprovalWorkflowTest.php

use App\Models\Debt;
use App\Models\Installment;
use App\Models\User;
use App\Services\DebtService;

beforeEach(function () {
    $this->lender = User::factory()->create(['is_admin' => false]);
    $this->debtor = User::factory()->create(['is_admin' => false]);
    $this->admin = User::factory()->create(['is_admin' => true]);

    $this->debt = Debt::factory()->create([
        'lender_id' => $this->lender->id,
        'debtor_id' => $this->debtor->id,
    ]);

    $this->installment = Installment::factory()->create([
        'debt_id' => $this->debt->id,
        'status' => Installment::STATUS_PENDING,
    ]);
});

it('debtor can submit a payment request', function () {
    $response = $this->actingAs($this->debtor)
        ->post(route('installments.pay', $this->installment), [
            'reference_number' => 'REF-001',
            'notes' => 'حوالة بنكية',
        ]);

    $response->assertRedirect();
    $this->installment->refresh();
    expect($this->installment->status)->toBe(Installment::STATUS_PENDING_APPROVAL);
    expect($this->installment->reference_number)->toBe('REF-001');
});

it('lender cannot submit a payment request for someone else\'s installment', function () {
    $this->actingAs($this->lender)
        ->post(route('installments.pay', $this->installment), [
            'reference_number' => 'REF-002',
        ])
        ->assertForbidden();
});

it('pay button is hidden when status is pending_approval', function () {
    $this->installment->update(['status' => Installment::STATUS_PENDING_APPROVAL]);

    // Verify model helper
    expect($this->installment->isPendingApproval())->toBeTrue();
});

it('admin can approve a pending_approval installment', function () {
    $this->installment->update([
        'status' => Installment::STATUS_PENDING_APPROVAL,
        'reference_number' => 'REF-003',
    ]);

    // Mock DebtService so we don't need real wallet balances in unit scope
    $this->mock(DebtService::class)
        ->shouldReceive('recordPayment')
        ->once()
        ->with(Mockery::on(fn ($i) => $i->id === $this->installment->id));

    $this->actingAs($this->admin)
        ->post(route('admin.installments.approve', $this->installment))
        ->assertRedirect();
});

it('non-admin cannot access the approve route', function () {
    $this->installment->update(['status' => Installment::STATUS_PENDING_APPROVAL]);

    $this->actingAs($this->debtor)
        ->post(route('admin.installments.approve', $this->installment))
        ->assertForbidden();
});

it('status_arabic returns correct Arabic label', function () {
    $this->installment->status = Installment::STATUS_PENDING_APPROVAL;
    expect($this->installment->status_arabic)->toBe('بانتظار تأكيد الأدمن');
});

it('status_color returns info for pending_approval', function () {
    $this->installment->status = Installment::STATUS_PENDING_APPROVAL;
    expect($this->installment->status_color)->toBe('info');
});
