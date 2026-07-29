<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\CostNormalizationService;

class CostNormalizationServiceTest extends TestCase
{
    public function test_monthly_recurring_normalization(): void
    {
        $service = new CostNormalizationService();
        $monthly = $service->calculateNormalizedMonthlyCost(20.00, 1, 'monthly_recurring');
        $this->assertEquals(20.00, $monthly);
    }

    public function test_annual_recurring_normalization(): void
    {
        $service = new CostNormalizationService();
        $monthly = $service->calculateNormalizedMonthlyCost(720.00, 12, 'annual_recurring');
        $this->assertEquals(60.00, $monthly);
        
        $annual = $service->calculateNormalizedAnnualCost($monthly);
        $this->assertEquals(720.00, $annual);
    }

    public function test_free_and_trial_normalization(): void
    {
        $service = new CostNormalizationService();
        $this->assertEquals(0.00, $service->calculateNormalizedMonthlyCost(0.00, 1, 'free'));
        $this->assertEquals(0.00, $service->calculateNormalizedMonthlyCost(20.00, 1, 'trial'));
    }
}
