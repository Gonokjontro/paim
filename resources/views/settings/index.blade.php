@extends('layouts.app')

@section('title', 'Workspace Settings & Config - PAIM')
@section('page_title', 'Workspace Settings & Configuration')

@section('content')
<div class="space-y-8">

    <!-- Header Summary -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">System Settings & Custom Configuration</h2>
            <p class="text-xs paim-subtitle">Configure currency symbols, alert thresholds, custom categories, vendor tools, and meter units</p>
        </div>
    </div>

    <!-- Section 1: General Workspace & Currency Configuration -->
    <div class="p-6 rounded-2xl paim-card space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center text-lg">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h3 class="font-bold paim-title text-base">Workspace & Regional Preferences</h3>
                    <p class="text-xs paim-subtitle">Base currency, symbol formatting, timezone, and fiscal calendar</p>
                </div>
            </div>
        </div>

        <form action="{{ route('settings.update-workspace') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Workspace Name</label>
                    <input type="text" name="name" value="{{ old('name', $workspace->name) }}" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Base Currency</label>
                    <select name="base_currency" onchange="updateCurrencySymbol(this)" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        @foreach($supportedCurrencies as $curr)
                            <option value="{{ $curr['code'] }}" data-symbol="{{ $curr['symbol'] }}" {{ $workspace->base_currency === $curr['code'] ? 'selected' : '' }}>
                                {{ $curr['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Currency Symbol</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', $workspace->currency_symbol ?? '$') }}" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm font-bold focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Workspace Timezone</label>
                    <select name="time_zone" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="UTC" {{ $workspace->time_zone === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                        <option value="America/New_York" {{ $workspace->time_zone === 'America/New_York' ? 'selected' : '' }}>US Eastern (America/New_York)</option>
                        <option value="America/Los_Angeles" {{ $workspace->time_zone === 'America/Los_Angeles' ? 'selected' : '' }}>US Pacific (America/Los_Angeles)</option>
                        <option value="Europe/London" {{ $workspace->time_zone === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                        <option value="Asia/Dhaka" {{ $workspace->time_zone === 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka (BST)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1.5">Fiscal Year Start Month</label>
                    <select name="fiscal_year_start_month" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="1" {{ $workspace->fiscal_year_start_month == 1 ? 'selected' : '' }}>January</option>
                        <option value="4" {{ $workspace->fiscal_year_start_month == 4 ? 'selected' : '' }}>April</option>
                        <option value="7" {{ $workspace->fiscal_year_start_month == 7 ? 'selected' : '' }}>July</option>
                        <option value="10" {{ $workspace->fiscal_year_start_month == 10 ? 'selected' : '' }}>October</option>
                    </select>
                </div>
            </div>

            <!-- Alert Threshold Config -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-4">
                <h4 class="font-bold paim-title text-sm">Default Alert Policies & Thresholds</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Warning Threshold (% of limit)</label>
                        <input type="number" name="warning_threshold_pct" value="{{ $workspace->getSetting('warning_threshold_pct', 80) }}" min="50" max="99" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Critical Threshold (% of limit)</label>
                        <input type="number" name="critical_threshold_pct" value="{{ $workspace->getSetting('critical_threshold_pct', 100) }}" min="80" max="150" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Alert Cool-down (Hours)</label>
                        <input type="number" name="cool_down_hours" value="{{ $workspace->getSetting('cool_down_hours', 24) }}" min="1" max="168" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    </div>
                </div>
            </div>

            @if(auth()->user()->role === 'admin')
            <div class="flex items-center justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Workspace Config</button>
            </div>
            @endif
        </form>
    </div>

    <!-- Section 2: Category Management -->
    <div class="p-6 rounded-2xl paim-card space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-500/20 flex items-center justify-center text-lg">
                    <i class="bi bi-tags-fill"></i>
                </div>
                <div>
                    <h3 class="font-bold paim-title text-base">Category Management</h3>
                    <p class="text-xs paim-subtitle">Configure subscription classification categories, icons, and color badges</p>
                </div>
            </div>
            @if(auth()->user()->role === 'admin')
            <button onclick="document.getElementById('catModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl paim-btn-primary text-xs font-semibold flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> Add Category
            </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($categories as $cat)
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm shadow-sm" style="background-color: {{ $cat->color }}">
                        <i class="bi {{ $cat->icon }}"></i>
                    </span>
                    <div>
                        <span class="font-bold paim-title text-sm block">{{ $cat->name }}</span>
                        <span class="text-xs paim-subtitle">{{ $cat->tools_count }} tools linked</span>
                    </div>
                </div>
                @if(auth()->user()->role === 'admin')
                <form action="{{ route('settings.delete-category', $cat->id) }}" method="POST" onsubmit="return confirm('Delete category?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-slate-400 hover:text-rose-500 p-1"><i class="bi bi-trash-fill text-sm"></i></button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Section 3: Vendor & Tool Inventory -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Vendors Card -->
        <div class="p-6 rounded-2xl paim-card space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                <h3 class="font-bold paim-title text-base">Registered AI & Software Vendors</h3>
                @if(auth()->user()->role === 'admin')
                <button onclick="document.getElementById('vendorModal').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg paim-btn-primary text-xs font-semibold">
                    + Add Vendor
                </button>
                @endif
            </div>

            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                @foreach($vendors as $vendor)
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold paim-title block">{{ $vendor->name }}</span>
                        <span class="paim-subtitle">{{ $vendor->website ?? 'No website' }}</span>
                    </div>
                    @if(auth()->user()->role === 'admin')
                    <form action="{{ route('settings.delete-vendor', $vendor->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="text-slate-400 hover:text-rose-500"><i class="bi bi-trash-fill"></i></button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Meter Units Card -->
        <div class="p-6 rounded-2xl paim-card space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                <h3 class="font-bold paim-title text-base">Custom Meter & Token Units</h3>
                @if(auth()->user()->role === 'admin')
                <button onclick="document.getElementById('meterModal').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg paim-btn-primary text-xs font-semibold">
                    + Add Meter Unit
                </button>
                @endif
            </div>

            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                @foreach($meterUnits as $meter)
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold paim-title block">{{ $meter->name }} ({{ $meter->symbol }})</span>
                        <span class="paim-subtitle">{{ $meter->description ?? 'Custom pricing meter' }}</span>
                    </div>
                    @if(auth()->user()->role === 'admin')
                    <form action="{{ route('settings.delete-meter-unit', $meter->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="text-slate-400 hover:text-rose-500"><i class="bi bi-trash-fill"></i></button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

<!-- Modal: Add Category -->
<div id="catModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
            <h3 class="font-bold paim-title">Add New Category</h3>
            <button onclick="document.getElementById('catModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg"></i></button>
        </div>

        <form action="{{ route('settings.store-category') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold paim-subtitle mb-1">Category Name</label>
                <input type="text" name="name" placeholder="e.g. AI Voice & Speech" required class="w-full paim-input rounded-xl px-3 py-2 text-sm focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold paim-subtitle mb-1">Badge Color (Hex)</label>
                    <input type="color" name="color" value="#818CF8" class="w-full h-10 paim-input rounded-xl p-1">
                </div>
                <div>
                    <label class="block text-xs font-semibold paim-subtitle mb-1">Icon Class</label>
                    <input type="text" name="icon" value="bi-stars" class="w-full paim-input rounded-xl px-3 py-2 text-sm focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('catModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg paim-btn-secondary text-xs">Cancel</button>
                <button type="submit" class="px-4 py-1.5 rounded-lg paim-btn-primary text-xs font-semibold">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Vendor -->
<div id="vendorModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
            <h3 class="font-bold paim-title">Add Vendor</h3>
            <button onclick="document.getElementById('vendorModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg"></i></button>
        </div>

        <form action="{{ route('settings.store-vendor') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold paim-subtitle mb-1">Vendor Name</label>
                <input type="text" name="name" placeholder="e.g. Anthropic" required class="w-full paim-input rounded-xl px-3 py-2 text-sm focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold paim-subtitle mb-1">Website URL</label>
                <input type="url" name="website" placeholder="https://anthropic.com" class="w-full paim-input rounded-xl px-3 py-2 text-sm focus:outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('vendorModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg paim-btn-secondary text-xs">Cancel</button>
                <button type="submit" class="px-4 py-1.5 rounded-lg paim-btn-primary text-xs font-semibold">Save Vendor</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Meter Unit -->
<div id="meterModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
            <h3 class="font-bold paim-title">Add Custom Meter Unit</h3>
            <button onclick="document.getElementById('meterModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg"></i></button>
        </div>

        <form action="{{ route('settings.store-meter-unit') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold paim-subtitle mb-1">Unit Name</label>
                <input type="text" name="name" placeholder="e.g. Audio Minutes" required class="w-full paim-input rounded-xl px-3 py-2 text-sm focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold paim-subtitle mb-1">Symbol / Label</label>
                <input type="text" name="symbol" placeholder="e.g. Mins" required class="w-full paim-input rounded-xl px-3 py-2 text-sm focus:outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('meterModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg paim-btn-secondary text-xs">Cancel</button>
                <button type="submit" class="px-4 py-1.5 rounded-lg paim-btn-primary text-xs font-semibold">Save Meter Unit</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCurrencySymbol(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    const symbol = selectedOption.getAttribute('data-symbol');
    if (symbol) {
        document.getElementById('currency_symbol').value = symbol;
    }
}
</script>
@endsection
