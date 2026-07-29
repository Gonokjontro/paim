<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Models\Alert;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookNotificationService
{
    public function dispatchAlertNotification(Alert $alert): int
    {
        $endpoints = WebhookEndpoint::where('workspace_id', $alert->workspace_id)
            ->where('is_active', true)
            ->get();

        $dispatchedCount = 0;

        foreach ($endpoints as $endpoint) {
            try {
                $success = match ($endpoint->channel_type) {
                    'discord' => $this->sendDiscordWebhook($endpoint, $alert),
                    'slack' => $this->sendSlackWebhook($endpoint, $alert),
                    'telegram' => $this->sendTelegramNotification($endpoint, $alert),
                    default => $this->sendCustomWebhook($endpoint, $alert),
                };

                if ($success) {
                    $endpoint->update(['last_triggered_at' => now()]);
                    $dispatchedCount++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to dispatch webhook to {$endpoint->name}: " . $e->getMessage());
            }
        }

        return $dispatchedCount;
    }

    public function sendTestPing(WebhookEndpoint $endpoint): bool
    {
        $dummyAlert = new Alert([
            'workspace_id' => $endpoint->workspace_id,
            'severity' => 'warning',
            'title' => 'PAIM Webhook Test Notification',
            'message' => 'Test ping successfully received from PAIM Webhook Engine at ' . now()->toDateTimeString(),
            'status' => 'unacknowledged',
        ]);

        return match ($endpoint->channel_type) {
            'discord' => $this->sendDiscordWebhook($endpoint, $dummyAlert),
            'slack' => $this->sendSlackWebhook($endpoint, $dummyAlert),
            'telegram' => $this->sendTelegramNotification($endpoint, $dummyAlert),
            default => $this->sendCustomWebhook($endpoint, $dummyAlert),
        };
    }

    private function sendDiscordWebhook(WebhookEndpoint $endpoint, Alert $alert): bool
    {
        $color = match ($alert->severity) {
            'critical' => 15548997, // Red
            'warning' => 16753920,  // Orange
            default => 5814783,     // Indigo
        };

        $payload = [
            'embeds' => [
                [
                    'title' => '🚨 ' . $alert->title,
                    'description' => $alert->message,
                    'color' => $color,
                    'footer' => [
                        'text' => 'PAIM AI Subscription Management System • ' . now()->format('M d, Y H:i'),
                    ],
                ]
            ]
        ];

        $response = Http::post($endpoint->webhook_url, $payload);
        return $response->successful();
    }

    private function sendSlackWebhook(WebhookEndpoint $endpoint, Alert $alert): bool
    {
        $payload = [
            'text' => "🚨 *PAIM Alert: {$alert->title}*\n{$alert->message}",
        ];

        $response = Http::post($endpoint->webhook_url, $payload);
        return $response->successful();
    }

    private function sendTelegramNotification(WebhookEndpoint $endpoint, Alert $alert): bool
    {
        // Telegram URL format: https://api.telegram.org/bot<TOKEN>/sendMessage?chat_id=<CHAT_ID>
        $text = "🚨 *PAIM Alert: {$alert->title}*\n{$alert->message}";
        $response = Http::post($endpoint->webhook_url, [
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
        return $response->successful();
    }

    private function sendCustomWebhook(WebhookEndpoint $endpoint, Alert $alert): bool
    {
        $payload = [
            'event' => 'alert_triggered',
            'severity' => $alert->severity,
            'title' => $alert->title,
            'message' => $alert->message,
            'timestamp' => now()->toIso8601String(),
        ];

        $response = Http::post($endpoint->webhook_url, $payload);
        return $response->successful();
    }
}
