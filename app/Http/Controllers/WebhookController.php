<?php

namespace App\Http\Controllers;

use App\Models\WebhookEndpoint;
use App\Services\WebhookNotificationService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected WebhookNotificationService $webhookService;

    public function __construct(WebhookNotificationService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function index()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $webhooks = WebhookEndpoint::where('workspace_id', $workspaceId)->get();

        return view('webhooks.index', compact('webhooks'));
    }

    public function store(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel_type' => 'required|in:discord,slack,telegram,custom',
            'webhook_url' => 'required|url',
        ]);

        WebhookEndpoint::create([
            'workspace_id' => $workspaceId,
            'name' => $validated['name'],
            'channel_type' => $validated['channel_type'],
            'webhook_url' => $validated['webhook_url'],
            'events' => ['renewal_warning', 'budget_exceeded', 'card_expiry'],
            'is_active' => true,
        ]);

        return redirect()->route('webhooks.index')->with('success', 'Webhook notification channel configured successfully.');
    }

    public function test(Request $request, $id)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $webhook = WebhookEndpoint::where('workspace_id', $workspaceId)->findOrFail($id);

        $success = $this->webhookService->sendTestPing($webhook);

        if ($success) {
            $webhook->update(['last_triggered_at' => now()]);
            return redirect()->route('webhooks.index')->with('success', "Test ping sent successfully to {$webhook->name}.");
        }

        return redirect()->route('webhooks.index')->with('error', "Failed to deliver test ping to {$webhook->name}. Please check the Webhook URL.");
    }

    public function destroy($id)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $webhook = WebhookEndpoint::where('workspace_id', $workspaceId)->findOrFail($id);
        $webhook->delete();

        return redirect()->route('webhooks.index')->with('success', 'Webhook channel deleted successfully.');
    }
}
