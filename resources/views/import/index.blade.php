@extends('layouts.app')

@section('title', 'Import & Audit Trail - PAIM')
@section('page_title', 'CSV Import & Immutable Audit Log')

@section('content')
<div class="space-y-6">

    <!-- CSV Batch Import Card -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <div>
                <h3 class="font-bold paim-title text-base">Batch CSV / XLSX Import Wizard</h3>
                <p class="text-xs paim-subtitle">Import historical subscription ledger charges and token usage entries with duplicate detection</p>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
        <form action="{{ route('import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="border-2 border-dashed border-slate-300 dark:border-slate-800 hover:border-indigo-500 rounded-2xl p-8 text-center space-y-3 transition-all">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center mx-auto">
                    <i class="bi bi-cloud-arrow-up-fill text-2xl"></i>
                </div>
                <div>
                    <span class="block text-sm font-semibold paim-title">Choose CSV or Excel File to Upload</span>
                    <span class="block text-xs paim-subtitle mt-0.5">Supports subscription list, cost entries, or token usage logs</span>
                </div>
                <input type="file" name="csv_file" required class="mx-auto text-xs paim-subtitle file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Process Import Batch</button>
            </div>
        </form>
        @else
        <div class="p-4 rounded-xl paim-badge-role text-xs font-medium">
            <i class="bi bi-lock-fill mr-1"></i> Admin privileges required to upload CSV import batches.
        </div>
        @endif
    </div>

    <!-- Batch History Table -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <h3 class="font-bold paim-title text-base">Import Batch Executions</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Batch File</th>
                        <th class="py-3.5 px-4">Total Rows</th>
                        <th class="py-3.5 px-4">Imported</th>
                        <th class="py-3.5 px-4">Failed</th>
                        <th class="py-3.5 px-4">Processed Date</th>
                        <th class="py-3.5 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($batches as $batch)
                    <tr>
                        <td class="py-3.5 px-4 font-semibold paim-title">
                            {{ $batch->file_name }}
                        </td>
                        <td class="py-3.5 px-4 text-xs font-bold paim-text">
                            {{ $batch->total_rows }}
                        </td>
                        <td class="py-3.5 px-4 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $batch->imported_rows }}
                        </td>
                        <td class="py-3.5 px-4 text-xs font-bold paim-subtitle">
                            {{ $batch->failed_rows }}
                        </td>
                        <td class="py-3.5 px-4 text-xs paim-subtitle">
                            {{ $batch->created_at ? $batch->created_at->format('M d, Y H:i') : 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold paim-badge-success">
                                {{ ucfirst($batch->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Immutable Audit Trail Log -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <h3 class="font-bold paim-title text-base">Immutable Audit Trail</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Timestamp</th>
                        <th class="py-3.5 px-4">Event Type</th>
                        <th class="py-3.5 px-4">Entity</th>
                        <th class="py-3.5 px-4">Entity ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @foreach($auditLogs as $log)
                    <tr>
                        <td class="py-3.5 px-4 text-xs paim-subtitle">
                            {{ $log->created_at ? $log->created_at->format('M d, Y H:i:s') : 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded text-xs font-semibold paim-badge-role">
                                {{ $log->event_type }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-semibold paim-title">
                            {{ $log->entity_type }}
                        </td>
                        <td class="py-3.5 px-4 text-xs paim-text font-mono">
                            #{{ $log->entity_id }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
