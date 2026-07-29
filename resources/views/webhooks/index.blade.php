@extends('layouts.app')

@section('title', 'Webhook Alerting - PAIM')
@section('page_title', 'Multi-Channel Webhook Notifications')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold paim-title">Webhook Alerting Channels</h2>
            <p class="text-xs paim-subtitle">Configure real-time alerts to Discord, Slack, Telegram, or custom API endpoints</p>
        </div>
        <button onclick="document.getElementById('addWebhookModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold flex items-center gap-2">
            <i class="bi bi-bell-plus-fill"></i>
            <span>Add Webhook Channel</span>
        </button>
    </div>

    <!-- Webhooks List -->
    <div class="p-6 rounded-2xl paim-card space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm paim-table">
                <thead class="text-xs uppercase font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Channel Name</th>
                        <th class="py-3.5 px-4">Platform</th>
                        <th class="py-3.5 px-4">Webhook Endpoint URL</th>
                        <th class="py-3.5 px-4">Last Triggered</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($webhooks as $webhook)
                    <tr>
                        <td class="py-4 px-4 font-bold paim-title">
                            {{ $webhook->name }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase paim-badge-role flex items-center gap-1.5 w-fit">
                                <i class="bi {{ $webhook->channel_type === 'discord' ? 'bi-discord' : ($webhook->channel_type === 'slack' ? 'bi-slack' : ($webhook->channel_type === 'telegram' ? 'bi-telegram' : 'bi-globe')) }}"></i>
                                {{ ucfirst($webhook->channel_type) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-xs font-mono paim-subtitle max-w-xs truncate">
                            {{ $webhook->webhook_url }}
                        </td>
                        <td class="py-4 px-4 text-xs paim-subtitle">
                            {{ $webhook->last_triggered_at ? $webhook->last_triggered_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Test Ping -->
                                <form action="{{ route('webhooks.test', $webhook->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" title="Send Test Ping" class="px-3 py-1.5 rounded-lg paim-btn-secondary text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                        <i class="bi bi-send-fill mr-1"></i> Test Ping
                                    </button>
                                </form>

                                <!-- Delete -->
                                <form action="{{ route('webhooks.destroy', $webhook->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete webhook channel {{ $webhook->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Webhook" class="p-2 rounded-lg paim-btn-secondary text-xs text-rose-600 dark:text-rose-400 hover:text-rose-700">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center paim-subtitle text-sm">
                            No webhook alerting channels configured yet. Click "Add Webhook Channel" to connect Discord, Slack, or Telegram!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Add Webhook -->
<div id="addWebhookModal" class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="paim-modal-box rounded-2xl max-w-lg w-full p-6 space-y-6 relative">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <h3 class="text-lg font-bold paim-title">Add Webhook Alerting Channel</h3>
            <button onclick="document.getElementById('addWebhookModal').classList.add('hidden')" class="paim-subtitle hover:text-rose-500"><i class="bi bi-x-lg text-lg"></i></button>
        </div>

        <form action="{{ route('webhooks.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Channel Name</label>
                <input type="text" name="name" placeholder="e.g. Discord Dev Alerts" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Platform Type</label>
                <select name="channel_type" required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
                    <option value="discord">Discord Webhook</option>
                    <option value="slack">Slack Webhook</option>
                    <option value="telegram">Telegram Bot</option>
                    <option value="custom">Custom HTTP Webhook Endpoint</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase paim-subtitle mb-1">Webhook Endpoint URL</label>
                <input type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..." required class="w-full paim-input rounded-xl px-4 py-2.5 text-sm focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('addWebhookModal').classList.add('hidden')" class="px-4 py-2 rounded-xl paim-btn-secondary text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl paim-btn-primary text-sm font-semibold">Save Channel</button>
            </div>
        </form>
    </div>
</div>
@endsection
