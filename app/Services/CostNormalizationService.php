<?php

namespace App\Services;

class CostNormalizationService
{
    /**
     * Calculate normalized monthly cost per Section 4.4 of BRD.
     * Normalized Monthly = Net recurring amount * 12 / billing interval in months.
     * Annual plans divide by 12.
     */
    public function calculateNormalizedMonthlyCost(float $recurringAmount, int $billingCadenceMonths = 1, string $type = 'monthly_recurring'): float
    {
        if (in_array($type, ['free', 'trial'])) {
            return 0.00;
        }

        if ($type === 'annual_recurring' || $billingCadenceMonths == 12) {
            return round($recurringAmount / 12.0, 4);
        }

        if ($billingCadenceMonths > 0) {
            return round(($recurringAmount * 12.0 / $billingCadenceMonths) / 12.0, 4);
        }

        return round($recurringAmount, 4);
    }

    /**
     * Calculate normalized annual cost: Normalized Monthly * 12
     */
    public function calculateNormalizedAnnualCost(float $normalizedMonthlyCost): float
    {
        return round($normalizedMonthlyCost * 12.0, 4);
    }

    /**
     * Convert currency with FX rate
     */
    public function convertCurrency(float $amount, float $fxRate = 1.0): float
    {
        return round($amount * $fxRate, 4);
    }
}
