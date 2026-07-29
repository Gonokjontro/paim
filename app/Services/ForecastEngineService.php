<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\CostEntry;
use App\Models\UsageEntry;
use Carbon\Carbon;

class ForecastEngineService
{
    protected CostNormalizationService $costNormalizer;

    public function __construct(CostNormalizationService $costNormalizer)
    {
        $this->costNormalizer = $costNormalizer;
    }

    /**
     * Run-rate forecast per Section 4.4: (Actual Period-to-Date / Elapsed Days) * Total Period Days
     */
    public function calculateRunRateForecast(float $actualPtd, int $elapsedDays, int $totalDaysInPeriod): float
    {
        if ($elapsedDays <= 0) {
            return $actualPtd;
        }

        $dailyRate = $actualPtd / $elapsedDays;
        return round($dailyRate * $totalDaysInPeriod, 4);
    }

    /**
     * Calculate committed costs for active subscriptions in a workspace
     */
    public function calculateCommittedMonthlyCost(int $workspaceId): float
    {
        $subscriptions = Subscription::where('workspace_id', $workspaceId)
            ->whereIn('status', ['active', 'trial'])
            ->with('currentPlanVersion')
            ->get();

        $totalCommittedMonthly = 0.0;

        foreach ($subscriptions as $sub) {
            $plan = $sub->currentPlanVersion;
            if ($plan) {
                $monthly = $this->costNormalizer->calculateNormalizedMonthlyCost(
                    (float) $plan->recurring_amount,
                    (int) $sub->billing_cadence_months,
                    $sub->type
                );
                $totalCommittedMonthly += $monthly;
            }
        }

        return round($totalCommittedMonthly, 4);
    }

    /**
     * Total period forecast: Actual posted + expected committed + estimated on-demand
     */
    public function calculateTotalMonthlyForecast(int $workspaceId, ?Carbon $month = null): float
    {
        $month = $month ?? Carbon::now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // 1. Actual posted cost entries in this month
        $actualPosted = (float) CostEntry::where('workspace_id', $workspaceId)
            ->whereBetween('posted_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'posted')
            ->sum('base_amount');

        // 2. Committed monthly cost
        $committed = $this->calculateCommittedMonthlyCost($workspaceId);

        // 3. On-demand usage run-rate forecast
        $elapsedDays = max(1, Carbon::now()->day);
        $totalDays = $month->daysInMonth;
        
        $tokenActual = (float) UsageEntry::where('workspace_id', $workspaceId)
            ->whereBetween('usage_date', [$startOfMonth, $endOfMonth])
            ->sum('calculated_cost');

        $tokenForecast = $this->calculateRunRateForecast($tokenActual, $elapsedDays, $totalDays);

        return round(max($actualPosted + $committed, $actualPosted + $tokenForecast), 4);
    }
}
