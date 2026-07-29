<?php

namespace App\Services;

use App\Models\Target;
use App\Models\Alert;
use App\Models\Subscription;
use App\Models\PaymentAccount;
use Carbon\Carbon;

class AlertEvaluationService
{
    protected ForecastEngineService $forecastService;

    public function __construct(ForecastEngineService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    /**
     * Evaluate targets and generate alerts for thresholds (Warning / Critical)
     */
    public function evaluateWorkspaceTargets(int $workspaceId): int
    {
        $targets = Target::where('workspace_id', $workspaceId)->where('status', 'active')->get();
        $generatedAlerts = 0;

        $forecastTotal = $this->forecastService->calculateTotalMonthlyForecast($workspaceId);

        foreach ($targets as $target) {
            if ($target->target_amount <= 0) {
                continue;
            }

            $utilizationPct = ($forecastTotal / $target->target_amount) * 100.0;

            if ($utilizationPct >= $target->critical_threshold_pct) {
                Alert::firstOrCreate([
                    'workspace_id' => $workspaceId,
                    'title' => "Critical Budget Alert: {$target->name}",
                    'status' => 'unacknowledged',
                ], [
                    'severity' => 'critical',
                    'message' => "Budget target '{$target->name}' ($" . number_format($target->target_amount, 2) . ") reached " . number_format($utilizationPct, 1) . "% utilization ($" . number_format($forecastTotal, 2) . ").",
                ]);
                $generatedAlerts++;
            } elseif ($utilizationPct >= $target->warning_threshold_pct) {
                Alert::firstOrCreate([
                    'workspace_id' => $workspaceId,
                    'title' => "Warning Budget Alert: {$target->name}",
                    'status' => 'unacknowledged',
                ], [
                    'severity' => 'warning',
                    'message' => "Budget target '{$target->name}' ($" . number_format($target->target_amount, 2) . ") reached " . number_format($utilizationPct, 1) . "% utilization ($" . number_format($forecastTotal, 2) . ").",
                ]);
                $generatedAlerts++;
            }
        }

        // Renewal & Card Expiry Checks
        $generatedAlerts += $this->evaluateRenewalsAndCards($workspaceId);

        return $generatedAlerts;
    }

    /**
     * Evaluate renewals within 7 days and expiring cards
     */
    public function evaluateRenewalsAndCards(int $workspaceId): int
    {
        $count = 0;
        $today = Carbon::now();
        $sevenDays = $today->copy()->addDays(7);

        // Subscriptions renewing soon
        $renewingSubs = Subscription::where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $sevenDays])
            ->get();

        foreach ($renewingSubs as $sub) {
            Alert::firstOrCreate([
                'workspace_id' => $workspaceId,
                'title' => "Upcoming Renewal: {$sub->name}",
                'status' => 'unacknowledged',
            ], [
                'severity' => 'info',
                'message' => "Subscription '{$sub->name}' is scheduled to renew on {$sub->end_date->format('M d, Y')}.",
            ]);
            $count++;
        }

        // Expiring Payment Accounts
        $expiringCards = PaymentAccount::where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->where('expiry_year', '<=', $today->year)
            ->where('expiry_month', '<=', $today->month)
            ->get();

        foreach ($expiringCards as $card) {
            Alert::firstOrCreate([
                'workspace_id' => $workspaceId,
                'title' => "Payment Card Expiring: {$card->friendly_name}",
                'status' => 'unacknowledged',
            ], [
                'severity' => 'warning',
                'message' => "Payment account '{$card->friendly_name}' ({$card->masked_identifier}) expires {$card->expiry_month}/{$card->expiry_year}.",
            ]);
            $count++;
        }

        return $count;
    }
}
