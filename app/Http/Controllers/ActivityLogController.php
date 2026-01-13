<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    // Removed manual check → now protected by route middleware('admin')

    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->module, fn($q, $module) => $q->where('module', $module))
            ->when($request->user_id, fn($q, $id) => $q->where('user_id', $id))
            ->latest()
            ->paginate(25);

        $modules = ActivityLog::distinct()->pluck('module');
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('admin.activity-logs.index', compact('logs', 'modules', 'users'));
        // If you moved view → use 'activity-logs.index'
    }
}