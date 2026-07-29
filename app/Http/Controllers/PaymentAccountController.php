<?php

namespace App\Http\Controllers;

use App\Models\PaymentAccount;
use App\Models\Subscription;
use Illuminate\Http\Request;

class PaymentAccountController extends Controller
{
    public function index()
    {
        $workspaceId = 1;
        $accounts = PaymentAccount::where('workspace_id', $workspaceId)->with('subscriptions.tool')->get();
        return view('payment_accounts.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'friendly_name' => 'required|string|max:255',
            'type' => 'required|string',
            'provider_issuer' => 'nullable|string',
            'last_four' => 'nullable|string|max:4',
            'expiry_month' => 'nullable|integer|min:1|max:12',
            'expiry_year' => 'nullable|integer|min:2026',
            'spend_limit' => 'nullable|numeric|min:0',
        ]);

        $workspaceId = 1;
        PaymentAccount::create([
            'workspace_id' => $workspaceId,
            'friendly_name' => $request->friendly_name,
            'type' => $request->type,
            'provider_issuer' => $request->provider_issuer,
            'masked_identifier' => $request->last_four ? "•••• {$request->last_four}" : null,
            'billing_currency' => 'USD',
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'status' => 'active',
            'spend_limit' => $request->spend_limit,
        ]);

        return redirect()->route('payment-accounts.index')->with('success', "Payment account '{$request->friendly_name}' created successfully.");
    }

    public function replace(Request $request, $id)
    {
        $request->validate(['target_account_id' => 'required|exists:payment_accounts,id']);

        $oldAccount = PaymentAccount::findOrFail($id);
        $newAccount = PaymentAccount::findOrFail($request->target_account_id);

        // Reassign all active subscriptions
        Subscription::where('payment_account_id', $oldAccount->id)
            ->update(['payment_account_id' => $newAccount->id]);

        $oldAccount->status = 'inactive';
        $oldAccount->save();

        return redirect()->route('payment-accounts.index')->with('success', "Subscriptions reassigned from {$oldAccount->friendly_name} to {$newAccount->friendly_name}.");
    }
}
