@extends('layouts.app')

@section('title', 'Role Permission Matrix - PAIM')
@section('page_title', 'Granular Role Permission Management')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Granular Role Permission Matrix</h2>
            <p class="text-xs paim-subtitle">Configure feature access control for Admin, Manager, and Viewer workspace roles</p>
        </div>
    </div>

    <!-- Permission Matrix Form Card -->
    <form action="{{ route('permissions.update') }}" method="POST" class="p-6 rounded-2xl paim-card space-y-6">
        @csrf

        @foreach($allPermissions as $module => $permissions)
        <div class="space-y-4 pt-2 first:pt-0 border-t border-slate-200 dark:border-slate-800 first:border-none">
            <h3 class="font-bold paim-title text-base flex items-center gap-2">
                <i class="bi bi-shield-check text-indigo-600 dark:text-indigo-400"></i>
                {{ $module }}
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm paim-table">
                    <thead class="text-xs uppercase font-semibold">
                        <tr>
                            <th class="py-3 px-4">Feature Permission</th>
                            <th class="py-3 px-4 text-center w-32">Admin Role</th>
                            <th class="py-3 px-4 text-center w-32">Manager Role</th>
                            <th class="py-3 px-4 text-center w-32">Viewer Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                        @foreach($permissions as $key => $label)
                        <tr>
                            <td class="py-3 px-4 font-medium paim-title">
                                {{ $label }}
                                <span class="block text-[11px] paim-subtitle font-mono">{{ $key }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" name="matrix[admin][]" value="{{ $key }}" {{ in_array($key, $matrix['admin'] ?? []) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" name="matrix[manager][]" value="{{ $key }}" {{ in_array($key, $matrix['manager'] ?? []) ? 'checked' : '' }} class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500">
                            </td>
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" name="matrix[viewer][]" value="{{ $key }}" {{ in_array($key, $matrix['viewer'] ?? []) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        <div class="flex items-center justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
            <button type="submit" class="px-6 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Permission Matrix</button>
        </div>
    </form>

</div>
@endsection
