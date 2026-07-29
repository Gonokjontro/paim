<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Tool;
use App\Models\MeterUnit;
use App\Models\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $workspace = Workspace::findOrFail($workspaceId);

        $categories = Category::where('workspace_id', $workspaceId)->withCount('tools')->get();
        $vendors = Vendor::where('workspace_id', $workspaceId)->withCount('tools')->get();
        $tools = Tool::where('workspace_id', $workspaceId)->with(['vendor', 'category'])->get();
        $meterUnits = MeterUnit::where('workspace_id', $workspaceId)->get();
        $targets = Target::where('workspace_id', $workspaceId)->get();

        $supportedCurrencies = [
            ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar ($)'],
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro (€)'],
            ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound (£)'],
            ['code' => 'JPY', 'symbol' => '¥', 'name' => 'Japanese Yen (¥)'],
            ['code' => 'CAD', 'symbol' => 'CA$', 'name' => 'Canadian Dollar (CA$)'],
            ['code' => 'AUD', 'symbol' => 'A$', 'name' => 'Australian Dollar (A$)'],
            ['code' => 'BDT', 'symbol' => '৳', 'name' => 'Bangladeshi Taka (৳)'],
            ['code' => 'INR', 'symbol' => '₹', 'name' => 'Indian Rupee (₹)'],
        ];

        return view('settings.index', compact('workspace', 'categories', 'vendors', 'tools', 'meterUnits', 'targets', 'supportedCurrencies'));
    }

    public function updateWorkspace(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;
        $workspace = Workspace::findOrFail($workspaceId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'time_zone' => 'required|string',
            'fiscal_year_start_month' => 'required|integer|min:1|max:12',
            'warning_threshold_pct' => 'required|integer|min:50|max:99',
            'critical_threshold_pct' => 'required|integer|min:80|max:150',
            'cool_down_hours' => 'required|integer|min:1|max:168',
        ]);

        $workspace->name = $request->name;
        $workspace->base_currency = $request->base_currency;
        $workspace->currency_symbol = $request->currency_symbol;
        $workspace->time_zone = $request->time_zone;
        $workspace->fiscal_year_start_month = $request->fiscal_year_start_month;
        $workspace->settings = [
            'warning_threshold_pct' => $request->warning_threshold_pct,
            'critical_threshold_pct' => $request->critical_threshold_pct,
            'cool_down_hours' => $request->cool_down_hours,
            'email_alerts_enabled' => $request->boolean('email_alerts_enabled'),
        ];
        $workspace->save();

        return redirect()->route('settings.index')->with('success', 'Workspace settings & configuration saved successfully.');
    }

    public function storeCategory(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'icon' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'workspace_id' => $workspaceId,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color,
            'icon' => $request->icon,
            'description' => $request->description,
        ]);

        return redirect()->route('settings.index')->with('success', "Category '{$request->name}' added.");
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        $category->delete();

        return redirect()->route('settings.index')->with('success', "Category '{$name}' deleted.");
    }

    public function storeVendor(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url',
            'support_email' => 'nullable|email',
        ]);

        Vendor::create([
            'workspace_id' => $workspaceId,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'website' => $request->website,
            'support_email' => $request->support_email,
        ]);

        return redirect()->route('settings.index')->with('success', "Vendor '{$request->name}' registered.");
    }

    public function deleteVendor($id)
    {
        $vendor = Vendor::findOrFail($id);
        $name = $vendor->name;
        $vendor->delete();

        return redirect()->route('settings.index')->with('success', "Vendor '{$name}' removed.");
    }

    public function storeTool(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'category_id' => 'nullable|exists:categories,id',
            'is_ai_tool' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        Tool::create([
            'workspace_id' => $workspaceId,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'vendor_id' => $request->vendor_id,
            'category_id' => $request->category_id,
            'is_ai_tool' => $request->boolean('is_ai_tool'),
            'description' => $request->description,
            'status' => 'active',
        ]);

        return redirect()->route('settings.index')->with('success', "Tool '{$request->name}' configured.");
    }

    public function deleteTool($id)
    {
        $tool = Tool::findOrFail($id);
        $name = $tool->name;
        $tool->delete();

        return redirect()->route('settings.index')->with('success', "Tool '{$name}' removed.");
    }

    public function storeMeterUnit(Request $request)
    {
        $workspaceId = auth()->user()->workspace_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        MeterUnit::create([
            'workspace_id' => $workspaceId,
            'name' => $request->name,
            'symbol' => $request->symbol,
            'description' => $request->description,
        ]);

        return redirect()->route('settings.index')->with('success', "Custom meter unit '{$request->name}' added.");
    }

    public function deleteMeterUnit($id)
    {
        $unit = MeterUnit::findOrFail($id);
        $name = $unit->name;
        $unit->delete();

        return redirect()->route('settings.index')->with('success', "Meter unit '{$name}' removed.");
    }
}
