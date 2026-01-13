@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/activity-log.svg') }}" width="32" height="32" alt="Inventory">
@endsection

@section('title', 'Activity Log')

@section('content')
            <!-- Activity Logs Tab -->
            <div id="activity-logs" class="tab-content">
                <h3 class="mb-2">Activity Logs</h3>
                <p class="text-muted mb-5">Monitor system activity and user actions</p>

                <div class="mb-4">
                    <form method="POST" action="{{ route('admin.activity.cleanup') }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                onclick="return confirm('Delete all logs older than 90 days?')">
                            Clear Old Logs (Testing)
                        </button>
                    </form>
                </div>

                @if($activities->isEmpty())
                    <div class="activity-card">
                        <div class="text-center py-5">
                            <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-history.svg') }}" 
                                 width="80" height="80" 
                                 class="mb-4 opacity-50" alt="No logs">
                            <p class="text-muted h5 mb-1">No activity logs to display at this time.</p>
                            <small class="text-muted">User actions will appear here as they occur.</small>
                        </div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                     style="background-color: var(--color-primary); width: 40px; height: 40px; font-size: 0.9rem;">
                                                    {{ $activity->causer ? $activity->causer->initials() : 'SY' }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $activity->causer ? $activity->causer->name : 'System' }}</div>
                                                    <small class="text-muted">{{ $activity->causer?->department ?? 'Unknown' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">{{ ucfirst($activity->event ?? 'unknown') }}</span></td>
                                        <td>{{ $activity->description }}</td>
                                        <td>{{ $activity->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection