<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\CostEntry;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\Category;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $orgId = $user->organization_id;

        $subscriptions = Subscription::with(['tool.vendor', 'tool.category', 'currentPlanVersion'])
            ->when($orgId, fn($q) => $q->where('organization_id', $orgId))
            ->where('status', 'active')
            ->get();

        $totalMonthlySpend = 0.00;
        $byVendor = [];
        $byCategory = [];

        foreach ($subscriptions as $sub) {
            $cost = $sub->currentPlanVersion ? (float) $sub->currentPlanVersion->normalized_monthly_amount : 0.00;
            $totalMonthlySpend += $cost;

            $vendorName = $sub->tool->vendor->name ?? 'Unassigned';
            $catName = $sub->tool->category->name ?? 'General';

            $byVendor[$vendorName] = ($byVendor[$vendorName] ?? 0.00) + $cost;
            $byCategory[$catName] = ($byCategory[$catName] ?? 0.00) + $cost;
        }

        return view('reports.index', compact('subscriptions', 'totalMonthlySpend', 'byVendor', 'byCategory'));
    }

    public function exportCsv()
    {
        $user = auth()->user();
        $orgId = $user->organization_id;

        $subscriptions = Subscription::with(['tool.vendor', 'tool.category', 'currentPlanVersion'])
            ->when($orgId, fn($q) => $q->where('organization_id', $orgId))
            ->get();

        $rows = [
            ['Subscription Name', 'Vendor', 'Category', 'Billing Cadence', 'Monthly Equivalent Amount', 'Status']
        ];

        foreach ($subscriptions as $sub) {
            $rows[] = [
                $sub->name,
                $sub->tool->vendor->name ?? 'N/A',
                $sub->tool->category->name ?? 'N/A',
                str_replace('_', ' ', ucfirst($sub->type)),
                '$' . number_format($sub->currentPlanVersion->normalized_monthly_amount ?? 0, 2),
                ucfirst($sub->status),
            ];
        }

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="paim_organization_report.csv"',
        ]);
    }
}
