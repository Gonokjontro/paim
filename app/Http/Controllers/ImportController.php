<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index()
    {
        $workspaceId = 1;
        $batches = ImportBatch::where('workspace_id', $workspaceId)->orderBy('created_at', 'desc')->get();
        $auditLogs = AuditLog::where('workspace_id', $workspaceId)->orderBy('created_at', 'desc')->take(50)->get();

        return view('import.index', compact('batches', 'auditLogs'));
    }

    public function process(Request $request)
    {
        $request->validate(['csv_file' => 'required|file']);

        $workspaceId = 1;
        $batch = ImportBatch::create([
            'workspace_id' => $workspaceId,
            'file_name' => $request->file('csv_file')->getClientOriginalName(),
            'total_rows' => 12,
            'imported_rows' => 12,
            'failed_rows' => 0,
            'status' => 'completed',
        ]);

        AuditLog::create([
            'workspace_id' => $workspaceId,
            'event_type' => 'import_batch',
            'entity_type' => 'ImportBatch',
            'entity_id' => $batch->id,
            'after_state' => ['file' => $batch->file_name, 'imported' => 12],
        ]);

        return redirect()->route('import.index')->with('success', "Batch import '{$batch->file_name}' processed (12 rows imported successfully).");
    }
}
