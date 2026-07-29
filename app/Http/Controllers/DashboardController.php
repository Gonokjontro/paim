<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Subscription;
use App\Models\CostEntry;
use App\Models\TokenPackage;
use App\Models\UsageEntry;
use App\Models\Target;
use App\Models\Alert;
use App\Models\Category;
use App\Services\ForecastEngineService;
use App\Services\CostNormalizationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ForecastEngineService $forecastService;
    protected CostNormalizationService $costNormalizer;

    public function __construct(ForecastEngineService $forecastService, CostNormalizationService $costNormalizer)
    {
        $this->forecastService = $forecastService;
        $this->costNormalizer = $costNormalizer;
    }

    public function index()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $workspace = Workspace::find($workspaceId);
        $currencySymbol = $workspace->currency_symbol ?? '$';

        $today = Carbon::now();

        // 1. KPI Cards
        $subscriptions = Subscription::where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->with(['tool.vendor', 'tool.category', 'paymentAccount', 'currentPlanVersion'])
            ->get();

        $activeToolsCount = $subscriptions->count();

        // Actual posted this month
        $actualPostedMonth = (float) CostEntry::where('workspace_id', $workspaceId)
            ->whereBetween('posted_date', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])
            ->where('status', 'posted')
            ->sum('base_amount');

        // Committed cost
        $committedCostMonthly = $this->forecastService->calculateCommittedMonthlyCost($workspaceId);

        // Total Forecast
        $totalForecastMonthly = $this->forecastService->calculateTotalMonthlyForecast($workspaceId, $today);

        // Target & Utilization %
        $target = Target::where('workspace_id', $workspaceId)->where('status', 'active')->first();
        $targetAmount = $target ? (float) $target->target_amount : 250.00;
        $utilizationPct = $targetAmount > 0 ? round(($totalForecastMonthly / $targetAmount) * 100, 1) : 0;

        // Renewal Reminders (renewing within 14 days)
        $renewalsCount = Subscription::where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $today->copy()->addDays(14)])
            ->count();

        // Active Alerts
        $alerts = Alert::where('workspace_id', $workspaceId)
            ->where('status', 'unacknowledged')
            ->orderBy('created_at', 'desc')
            ->get();

        // Categories breakdown
        $categories = Category::where('workspace_id', $workspaceId)->with('tools.subscriptions.currentPlanVersion')->get();
        $categoryBreakdown = [];
        foreach ($categories as $cat) {
            $catTotal = 0;
            foreach ($cat->tools as $tool) {
                foreach ($tool->subscriptions as $sub) {
                    if ($sub->status === 'active' && $sub->currentPlanVersion) {
                        $catTotal += $this->costNormalizer->calculateNormalizedMonthlyCost(
                            (float) $sub->currentPlanVersion->recurring_amount,
                            (int) $sub->billing_cadence_months,
                            $sub->type
                        );
                    }
                }
            }
            if ($catTotal > 0) {
                $categoryBreakdown[] = [
                    'name' => $cat->name,
                    'color' => $cat->color,
                    'amount' => $catTotal,
                ];
            }
        }

        // Active Token Packages
        $tokenPackages = TokenPackage::where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->with('subscription.tool')
            ->get();

        return view('dashboard', compact(
            'workspace',
            'currencySymbol',
            'subscriptions',
            'activeToolsCount',
            'actualPostedMonth',
            'committedCostMonthly',
            'totalForecastMonthly',
            'targetAmount',
            'utilizationPct',
            'renewalsCount',
            'alerts',
            'categoryBreakdown',
            'tokenPackages'
        ));
    }
}
