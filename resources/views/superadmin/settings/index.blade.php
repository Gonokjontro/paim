@extends('layouts.app')

@section('title', 'Global SaaS Config - PAIM Super Admin')
@section('page_title', 'SaaS Platform Global Configuration')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <h2 class="text-xl font-bold paim-title">Global SaaS Settings & Default Quotas</h2>
        <p class="text-xs paim-subtitle">Configure system-wide SaaS platform behavior, multi-tenant default limits, and branding</p>

        <form action="#" method="POST" onsubmit="alert('SaaS Global Configuration Saved.'); return false;" class="space-y-4 pt-4 border-t border-slate-200 dark:border-slate-800">
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Platform Brand Name</label>
                <input type="text" value="PAIM B2B SaaS Platform" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Default Starter Max Users</label>
                    <input type="number" value="5" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Default Pro Max Users</label>
                    <input type="number" value="25" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Global Platform Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
