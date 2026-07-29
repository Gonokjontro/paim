<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    public function index()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $subscriptions = Subscription::with(['tool', 'currentPlanVersion'])
            ->where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->get();

        $events = [];
        $today = Carbon::today();

        foreach ($subscriptions as $sub) {
            $cost = $sub->currentPlanVersion ? $sub->currentPlanVersion->normalized_monthly_amount : 0.00;
            $renewalDate = $sub->end_date ? Carbon::parse($sub->end_date) : null;
            $daysLeft = $renewalDate ? (int) $today->diffInDays($renewalDate, false) : null;

            $events[] = [
                'id' => $sub->id,
                'title' => $sub->name,
                'tool' => $sub->tool->name ?? 'Tool',
                'amount' => $cost,
                'type' => $sub->type,
                'renewal_date' => $renewalDate ? $renewalDate->format('Y-m-d') : 'N/A',
                'days_left' => $daysLeft,
                'is_urgent' => ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7),
            ];
        }

        // Sort events by renewal date
        usort($events, function ($a, $b) {
            if ($a['days_left'] === null) return 1;
            if ($b['days_left'] === null) return -1;
            return $a['days_left'] <=> $b['days_left'];
        });

        return view('calendar.index', compact('events'));
    }

    public function exportIcal()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $subscriptions = Subscription::with(['tool', 'currentPlanVersion'])
            ->where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->get();

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//PAIM//AI Subscription Renewal Calendar//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:PAIM Subscriptions Renewal Schedule\r\n";

        foreach ($subscriptions as $sub) {
            if (!$sub->end_date) continue;

            $cost = $sub->currentPlanVersion ? number_format($sub->currentPlanVersion->normalized_monthly_amount, 2) : '0.00';
            $startDateStr = Carbon::parse($sub->end_date)->format('Ymd\THis\Z');
            $endDateStr = Carbon::parse($sub->end_date)->addHour()->format('Ymd\THis\Z');

            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:sub-" . $sub->id . "@paim.ai\r\n";
            $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
            $ics .= "DTSTART:" . $startDateStr . "\r\n";
            $ics .= "DTEND:" . $endDateStr . "\r\n";
            $ics .= "SUMMARY:Renewal: " . $sub->name . " ($" . $cost . ")\r\n";
            $ics .= "DESCRIPTION:AI Subscription " . $sub->name . " is scheduled for renewal. Amount: $" . $cost . "\r\n";
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="paim_renewals.ics"',
        ]);
    }
}
