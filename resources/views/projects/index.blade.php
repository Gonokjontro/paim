@extends('layouts.app')

@section('title', 'Projects & Tax Write-off - PAIM')
@section('page_title', 'Team Projects & Tax Expense Allocation')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Projects & Business Tax Write-offs</h2>
            <p class="text-xs paim-subtitle">Allocate subscription costs across team projects and export business tax-deductible expense reports</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.export-tax') }}" class="px-4 py-2.5 rounded-xl paim-btn-secondary text-sm font-semibold flex items-center gap-2">
                <i class="bi bi-file-earmark-spreadsheet-fill text-emerald-500"></i>
                <span>Export Tax Write-off CSV</span>
            </a>
            @if(auth()->user()->role !== 'viewer')
            <button onclick="document.getElementById('addProjectModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
                <i class="bi bi-folder-plus"></i>
                <span>Create Project</span>
            </button>
            @endif
        </div>
    </div>

    <!-- KPI Summary Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-folder-fill"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold paim-title">{{ $projects->count() }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Active Projects</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-[shield-check]"></i>
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">${{ number_format($taxDeductibleTotal, 2) }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Eligible Tax Write-offs / Mo</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl paim-card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-pie-chart-fill"></i>
            </div>
            <div>
                <span class="block text-2xl font-extrabold paim-title">{{ $projects->sum(fn($p) => $p->allocations->count()) }}</span>
                <span class="text-xs paim-subtitle uppercase font-semibold">Allocated Subscriptions</span>
            </div>
        </div>
    </div>

    <!-- Projects List -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($projects as $project)
        <div class="p-6 rounded-2xl paim-card space-y-4 border-l-4" style="border-left-color: {{ $project->color }};">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold paim-title text-base flex items-center gap-2">
                        {{ $project->name }}
                        @if($project->is_tax_deductible)
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase paim-badge-success">Tax Write-off Eligible</span>
                        @endif
                    </h3>
                    <p class="text-xs paim-subtitle">Client: {{ $project->client_name ?? 'Internal Team' }}</p>
                </div>
                <div class="text-right">
                    <span class="block text-xs paim-subtitle uppercase">Budget Ceiling</span>
                    <strong class="paim-title text-base">${{ number_format($project->budget, 2) }}</strong>
                </div>
            </div>

            <!-- Allocations Table -->
            <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between text-xs font-semibold paim-subtitle uppercase">
                    <span>Allocated Subscription</span>
                    <span>Cost (% Split)</span>
                </div>

                @forelse($project->allocations as $alloc)
                <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-slate-100 dark:bg-slate-900/60">
                    <span class="font-medium paim-title">{{ $alloc->subscription->name ?? 'Subscription' }}</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">
                        ${{ number_format($alloc->allocated_amount, 2) }} ({{ $alloc->allocation_percentage }}%)
                    </span>
                </div>
                @empty
                <p class="text-xs paim-subtitle italic py-1">No subscriptions allocated to this project yet.</p>
                @endforelse
            </div>

            @if(auth()->user()->role !== 'viewer')
            <div class="pt-2 flex justify-end">
                <button onclick="openAllocateModal({{ $project->id }}, '{{ $project->name }}')" class="px-3 py-1.5 rounded-lg paim-btn-secondary text-xs font-semibold flex items-center gap-1.5">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Allocate Subscription</span>
                </button>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full p-12 text-center paim-card rounded-2xl">
            <i class="bi bi-folder-x text-4xl text-slate-400 mb-3 block"></i>
            <p class="paim-title font-bold text-base">No team projects created yet.</p>
        </div>
        @endforelse
    </div>

</div>

<!-- Modal: Create Project -->
<div id="addProjectModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Create Team Project Workspace</h3>
            <button onclick="document.getElementById('addProjectModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Project Name</label>
                <input type="text" name="name" placeholder="e.g. AI Customer Support Bot" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Client Name (Optional)</label>
                    <input type="text" name="client_name" placeholder="Acme Corp" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Monthly Budget Limit ($)</label>
                    <input type="number" step="0.01" name="budget" value="100.00" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Badge Color</label>
                    <input type="color" name="color" value="#6366F1" class="h-10 w-full rounded-xl cursor-pointer p-1 bg-transparent">
                </div>
                <div class="flex items-center gap-2 pt-4">
                    <input type="checkbox" name="is_tax_deductible" id="is_tax" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                    <label for="is_tax" class="text-xs font-semibold paim-title">Eligible Business Tax Write-off</label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addProjectModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Create Project</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Allocate Subscription -->
<div id="allocateModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-md w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Allocate Subscription to Project</h3>
            <button onclick="document.getElementById('allocateModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('projects.allocate') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="project_id" id="allocProjectId">
            <p class="text-xs paim-subtitle">Allocating cost for project <strong id="allocProjectName" class="paim-title"></strong>.</p>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Select Subscription</label>
                <select name="subscription_id" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    @foreach($subscriptions as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->name }} (${{ number_format($sub->currentPlanVersion->normalized_monthly_amount ?? 0, 2) }}/mo)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Allocation Percentage (%)</label>
                <input type="number" name="allocation_percentage" value="100" min="1" max="100" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('allocateModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Allocation</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAllocateModal(projectId, projectName) {
    document.getElementById('allocProjectId').value = projectId;
    document.getElementById('allocProjectName').innerText = projectName;
    document.getElementById('allocateModal').classList.remove('hidden');
}
</script>
@endsection
