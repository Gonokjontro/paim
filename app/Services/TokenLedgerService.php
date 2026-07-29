<?php

namespace App\Services;

use App\Models\TokenPackage;
use App\Models\UsageAllocation;
use App\Models\UsageEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TokenLedgerService
{
    /**
     * FIFO-by-expiry token lot allocation engine per BR-044
     */
    public function allocateUsageFIFO(UsageEntry $usageEntry): float
    {
        return DB::transaction(function () use ($usageEntry) {
            $remainingToAllocate = (float) $usageEntry->unit_count;
            $totalEffectiveCost = 0.0;

            // Fetch active packages for this subscription & meter sorted by expiry (earliest first)
            $packages = TokenPackage::where('workspace_id', $usageEntry->workspace_id)
                ->where('subscription_id', $usageEntry->subscription_id)
                ->where('meter_unit_id', $usageEntry->meter_unit_id)
                ->where('status', 'active')
                ->where('remaining_units', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($packages as $pkg) {
                if ($remainingToAllocate <= 0) {
                    break;
                }

                $available = (float) $pkg->remaining_units;
                $take = min($remainingToAllocate, $available);

                // Effective unit price for package = purchase_cost / granted_units
                $unitCost = $pkg->granted_units > 0 ? ($pkg->purchase_cost / $pkg->granted_units) : 0;
                $effectiveCost = round($take * $unitCost, 4);

                // Record allocation
                UsageAllocation::create([
                    'usage_entry_id' => $usageEntry->id,
                    'token_package_id' => $pkg->id,
                    'allocated_units' => $take,
                    'effective_cost' => $effectiveCost,
                ]);

                // Update package balance
                $pkg->consumed_units += $take;
                $pkg->remaining_units -= $take;
                if ($pkg->remaining_units <= 0) {
                    $pkg->status = 'exhausted';
                }
                $pkg->save();

                $remainingToAllocate -= $take;
                $totalEffectiveCost += $effectiveCost;
            }

            // Update usage entry calculated cost
            $usageEntry->calculated_cost = round($totalEffectiveCost, 4);
            $usageEntry->save();

            return round($totalEffectiveCost, 4);
        });
    }
}
