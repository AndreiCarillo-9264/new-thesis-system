<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->department !== 'admin') {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->module, function ($query, $module) {
                return $query->where('module', $module);
            })
            ->when($request->user_id, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->latest()
            ->paginate(25);

        // For filter dropdowns
        $modules = ActivityLog::distinct()->pluck('module');
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('activity_logs.index', compact('logs', 'modules', 'users'));
    }

    // Usually no create/edit/destroy – just view
}