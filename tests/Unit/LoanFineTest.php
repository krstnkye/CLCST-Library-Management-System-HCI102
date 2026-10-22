<?php

namespace Tests\Unit;

use App\Models\Loan;
use Tests\TestCase;

class LoanFineTest extends TestCase
{
    private function loan(string $due, ?string $returned = null): Loan
    {
        return new Loan(['borrowed_at' => now()->subDays(20), 'due_at' => $due, 'returned_at' => $returned]);
    }

    public function test_on_time_and_same_day_returns_have_no_fine(): void
    {
        $this->assertSame(0, $this->loan(now()->addDays(3))->daysLate());
        $this->assertSame(0, $this->loan(now()->subDays(2)->endOfDay(), now()->subDays(2)->setTime(16, 0))->daysLate());
        $this->assertSame(0.0, $this->loan(now()->endOfDay())->accruedFine());
    }

    public function test_fines_grow_per_day_and_stop_at_the_cap(): void
    {
        $this->assertSame(3, $this->loan(now()->subDays(3))->daysLate());
        $this->assertSame(15.0, $this->loan(now()->subDays(3))->accruedFine());
        $this->assertSame(200.0, $this->loan(now()->subDays(365))->accruedFine());
    }

    public function test_overdue_loans_cannot_be_renewed(): void
    {
        $this->assertTrue($this->loan(now()->subDay())->isOverdue());
        $this->assertFalse($this->loan(now()->subDay())->canRenew());
        $this->assertTrue($this->loan(now()->addDay())->canRenew());
    }
}
