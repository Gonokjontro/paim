@extends('layouts.app')

@section('title', 'Payment Accounts - PAIM')
@section('page_title', 'Payment Source Governance')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Payment Accounts</h2>
            <p class="text-xs paim-subtitle">Masked credit cards, bank accounts, virtual cards, wallets, and reimbursement accounts</p>
        </div>
        @if(auth()->user()->role !== 'viewer')
        <button onclick="document.getElementById('addAccountModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold transition-all flex items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Add Payment Account</span>
        </button>
        @else
        <span class="px-3 py-1.5 rounded-xl paim-badge-role text-xs font-medium">
            <i class="bi bi-lock-fill mr-1"></i> Read-Only Mode (Viewer Role)
        </span>
        @endif
    </div>

    <!-- Payment Accounts Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($accounts as $account)
        <div class="p-6 rounded-2xl paim-card relative overflow-hidden space-y-4 border {{ $account->status === 'expiring_soon' ? 'border-amber-400 dark:border-amber-500/50' : 'border-slate-200 dark:border-slate-800' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-slate-800 border border-indigo-100 dark:border-slate-700 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg">
                        <i class="bi bi-credit-card-2-front-fill"></i>
                    </div>
                    <div>
                        <h3 class="font-bold paim-title text-base">{{ $account->friendly_name }}</h3>
                        <span class="text-xs paim-subtitle font-mono">{{ $account->masked_identifier }}</span>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $account->status === 'active' ? 'paim-badge-success' : 'paim-badge-warning' }}">
                    {{ ucfirst(str_replace('_', ' ', $account->status)) }}
                </span>
            </div>

            <div class="space-y-2 text-xs pt-2 border-t border-slate-200 dark:border-slate-800/80">
                <div class="flex items-center justify-between paim-subtitle">
                    <span>Provider / Issuer:</span>
                    <span class="font-medium paim-title">{{ $account->provider_issuer ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center justify-between paim-subtitle">
                    <span>Expiry Date:</span>
                    <span class="font-semibold {{ $account->status === 'expiring_soon' ? 'text-amber-600 dark:text-amber-400 font-bold' : 'paim-title' }}">
                        {{ $account->expiry_month }}/{{ $account->expiry_year }}
                    </span>
                </div>
                <div class="flex items-center justify-between paim-subtitle">
                    <span>Monthly Spend Limit:</span>
                    <span class="font-semibold paim-title">${{ number_format($account->spend_limit ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center justify-between paim-subtitle">
                    <span>Linked Subscriptions:</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $account->subscriptions->count() }} active</span>
                </div>
            </div>

            <!-- Action Button for Admin Only -->
            @if(auth()->user()->role === 'admin')
            <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end">
                <button onclick="openReplaceModal({{ $account->id }}, '{{ $account->friendly_name }}')" class="px-3 py-1.5 rounded-lg paim-btn-secondary text-xs font-medium transition-all">
                    Reassign / Replace Card
                </button>
            </div>
            @endif
        </div>
        @endforeach
    </div>

</div>

<!-- Modal: Add Payment Account -->
@if(auth()->user()->role !== 'viewer')
<div id="addAccountModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Add Payment Account</h3>
            <button onclick="document.getElementById('addAccountModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('payment-accounts.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Friendly Name</label>
                <input type="text" name="friendly_name" placeholder="e.g. Chase Visa Reserve" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Account Type</label>
                    <select name="type" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                        <option value="card">Credit / Debit Card</option>
                        <option value="bank">Bank Account</option>
                        <option value="wallet">Digital Wallet / PayPal</option>
                        <option value="virtual_card">Virtual Card</option>
                        <option value="vendor_balance">Vendor Balance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Provider / Issuer</label>
                    <input type="text" name="provider_issuer" placeholder="e.g. Chase" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Last 4 Digits</label>
                    <input type="text" name="last_four" maxlength="4" placeholder="4242" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Expiry Month</label>
                    <input type="number" name="expiry_month" min="1" max="12" placeholder="12" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Expiry Year</label>
                    <input type="number" name="expiry_year" min="2026" max="2035" placeholder="2028" class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addAccountModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Account</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Modal: Reassign / Replace Account (Admin Only) -->
@if(auth()->user()->role === 'admin')
<div id="replaceModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Reassign Subscriptions to New Card</h3>
            <button onclick="document.getElementById('replaceModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form id="replaceForm" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs paim-subtitle">Select the replacement payment account. All active subscriptions attached to <strong id="oldAccountName" class="paim-title"></strong> will be reassigned automatically.</p>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">New Payment Account</label>
                <select name="target_account_id" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->friendly_name }} ({{ $acc->masked_identifier }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('replaceModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Reassign Subscriptions</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReplaceModal(accountId, accountName) {
    document.getElementById('oldAccountName').innerText = accountName;
    document.getElementById('replaceForm').action = "/payment-accounts/" + accountId + "/replace";
    document.getElementById('replaceModal').classList.remove('hidden');
}
</script>
@endif
@endsection
