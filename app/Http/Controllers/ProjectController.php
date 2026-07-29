<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAllocation;
use App\Models\Subscription;
use App\Models\CostEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function index()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $projects = Project::with(['allocations.subscription.tool'])
            ->where('workspace_id', $workspaceId)
            ->get();

        $subscriptions = Subscription::with('tool')
            ->where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->get();

        $taxDeductibleTotal = 0.00;
        foreach ($projects as $project) {
            if ($project->is_tax_deductible) {
                foreach ($project->allocations as $alloc) {
                    $taxDeductibleTotal += (float) $alloc->allocated_amount;
                }
            }
        }

        return view('projects.index', compact('projects', 'subscriptions', 'taxDeductibleTotal'));
    }

    public function store(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'budget' => 'required|numeric|min:0',
            'color' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        Project::create([
            'workspace_id' => $workspaceId,
            'name' => $validated['name'],
            'client_name' => $validated['client_name'],
            'budget' => $validated['budget'],
            'color' => $validated['color'],
            'is_tax_deductible' => $request->has('is_tax_deductible'),
            'description' => $validated['description'],
        ]);

        return redirect()->route('projects.index')->with('success', 'Project workspace created successfully.');
    }

    public function allocate(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'subscription_id' => 'required|exists:subscriptions,id',
            'allocation_percentage' => 'required|numeric|min:1|max:100',
        ]);

        $sub = Subscription::with('currentPlanVersion')->findOrFail($validated['subscription_id']);
        $monthlyCost = $sub->currentPlanVersion ? (float) $sub->currentPlanVersion->normalized_monthly_amount : 0.00;
        $allocatedAmount = ($monthlyCost * (float) $validated['allocation_percentage']) / 100.0;

        ProjectAllocation::updateOrCreate(
            [
                'workspace_id' => $workspaceId,
                'project_id' => $validated['project_id'],
                'subscription_id' => $validated['subscription_id'],
            ],
            [
                'allocation_percentage' => $validated['allocation_percentage'],
                'allocated_amount' => $allocatedAmount,
            ]
        );

        return redirect()->route('projects.index')->with('success', 'Subscription cost allocation updated.');
    }

    public function exportTaxReport()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $projects = Project::with(['allocations.subscription.tool'])
            ->where('workspace_id', $workspaceId)
            ->where('is_tax_deductible', true)
            ->get();

        $csvHeader = ["Project Name", "Client Name", "Subscription Tool", "Allocation %", "Monthly Cost (Base Currency)", "Tax Write-off Status"];
        $rows = [$csvHeader];

        foreach ($projects as $proj) {
            foreach ($proj->allocations as $alloc) {
                $rows[] = [
                    $proj->name,
                    $proj->client_name ?? 'Internal',
                    $alloc->subscription->name ?? 'Subscription',
                    $alloc->allocation_percentage . '%',
                    '$' . number_format($alloc->allocated_amount, 2),
                    'Eligible Business Tax Write-off',
                ];
            }
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
            'Content-Disposition' => 'attachment; filename="paim_tax_deductible_expenses.csv"',
        ]);
    }
}
