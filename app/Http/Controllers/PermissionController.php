<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $workspace = Workspace::findOrFail($workspaceId);

        $defaultPermissions = [
            'admin' => [
                'subscriptions.view', 'subscriptions.create', 'subscriptions.edit',
                'usage.view', 'usage.log', 'usage.purchase_package',
                'payment_accounts.view', 'payment_accounts.create', 'payment_accounts.replace',
                'targets.view', 'targets.create', 'alerts.acknowledge',
                'import.upload', 'audit.view', 'users.manage', 'settings.manage', 'permissions.manage',
            ],
            'manager' => [
                'subscriptions.view', 'subscriptions.create', 'subscriptions.edit',
                'usage.view', 'usage.log', 'usage.purchase_package',
                'payment_accounts.view', 'payment_accounts.create',
                'targets.view', 'targets.create', 'alerts.acknowledge',
                'audit.view',
            ],
            'viewer' => [
                'subscriptions.view', 'usage.view', 'payment_accounts.view',
                'targets.view', 'audit.view',
            ],
        ];

        $matrix = $workspace->getSetting('permission_matrix', $defaultPermissions);

        $allPermissions = [
            'Subscriptions' => [
                'subscriptions.view' => 'View Subscriptions & Plans',
                'subscriptions.create' => 'Create Subscriptions',
                'subscriptions.edit' => 'Update Subscription Status',
            ],
            'Token Usage & Credit Ledger' => [
                'usage.view' => 'View Usage Logs & Token Balances',
                'usage.log' => 'Log Token & Model Metered Usage',
                'usage.purchase_package' => 'Purchase Token Credit Packages',
            ],
            'Payment Source Governance' => [
                'payment_accounts.view' => 'View Card & Payment Inventory',
                'payment_accounts.create' => 'Add Payment Accounts',
                'payment_accounts.replace' => 'Reassign / Replace Cards (Admin Only)',
            ],
            'Budgets & Alert Policy' => [
                'targets.view' => 'View Spending Targets & Alerts',
                'targets.create' => 'Create Budget Targets',
                'alerts.acknowledge' => 'Acknowledge Alerts',
            ],
            'Import & Audit Trail' => [
                'import.upload' => 'Upload Batch CSV Files',
                'audit.view' => 'View Immutable Audit Trail Logs',
            ],
            'System Administration' => [
                'users.manage' => 'Manage Team Users & Roles',
                'settings.manage' => 'Configure Workspace & Currency',
                'permissions.manage' => 'Configure Permission Matrix',
            ],
        ];

        return view('permissions.index', compact('workspace', 'matrix', 'allPermissions'));
    }

    public function updateMatrix(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $workspace = Workspace::findOrFail($workspaceId);

        $matrix = [
            'admin' => $request->input('matrix.admin', []),
            'manager' => $request->input('matrix.manager', []),
            'viewer' => $request->input('matrix.viewer', []),
        ];

        // Ensure admin retains permissions.manage and users.manage
        if (!in_array('permissions.manage', $matrix['admin'])) {
            $matrix['admin'][] = 'permissions.manage';
        }
        if (!in_array('users.manage', $matrix['admin'])) {
            $matrix['admin'][] = 'users.manage';
        }

        $settings = $workspace->settings ?? [];
        $settings['permission_matrix'] = $matrix;
        $workspace->settings = $settings;
        $workspace->save();

        AuditLog::create([
            'workspace_id' => $workspaceId,
            'event_type' => 'permission_matrix_updated',
            'entity_type' => 'Workspace',
            'entity_id' => $workspaceId,
        ]);

        return redirect()->route('permissions.index')->with('success', 'Role Permission Matrix updated and applied successfully.');
    }
}
