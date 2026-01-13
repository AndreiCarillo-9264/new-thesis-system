@extends('layouts.app')

@section('page-icon')
    <img src="{{ asset('assets/icons/user-management.svg') }}" width="32" height="32" alt="Inventory">
@endsection

@section('title', 'User Management')

@section('content')
<div class="container-fluid px-4">
    <!-- Stats Cards - 8 Functional Stats -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Active Users</h5>
                <h2>{{ $activeUsers }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Active Sessions</h5>
                <h2>{{ $activeSessions }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>New Users Today</h5>
                <h2>{{ $newUsersToday }}</h2>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="stats-card">
                <h5>Total Activity Logs</h5>
                <h2>{{ $totalActivityLogs }}</h2>
            </div>
        </div>
    </div>

    <!-- Main Section Card -->
    <div class="section-card flex-fill">

        <div class="section-card-body">
            <!-- User Management Tab -->
            <div id="user-management" class="tab-content active">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>User Management</h3>
                    <button type="button" id="btn-add-user" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-pill"
                            data-bs-toggle="modal" data-bs-target="#addUserModal"
                            style="background-color: var(--color-primary); border-color: var(--color-primary);">
                        <img src="{{ asset('assets/vendor/boxicons/svg/basic/bx-plus.svg') }}" width="18" height="18" alt="Add User">
                        Add User
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="capitalize">{{ $user->department }}</td>
                                <td><span class="text-success">Active</span></td>
                                <td>{{ $user->updated_at->diffForHumans() }}</td>
                                <td>
                                    <button type="button" class="text-primary me-3 btn btn-link p-0" 
                                            data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                        Edit
                                    </button>
                                    <button type="button" class="text-danger btn btn-link p-0" 
                                            data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    No users to display yet.<br>
                                    <small>Users will appear here when added to the system.</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department *</label>
                            <select name="department" class="form-select" required>
                                <option value="">Select department</option>
                                <option value="sales" {{ old('department') == 'sales' ? 'selected' : '' }}>Sales</option>
                                <option value="production" {{ old('department') == 'production' ? 'selected' : '' }}>Production</option>
                                <option value="inventory" {{ old('department') == 'inventory' ? 'selected' : '' }}>Inventory</option>
                                <option value="logistics" {{ old('department') == 'logistics' ? 'selected' : '' }}>Logistics</option>
                                <option value="admin" {{ old('department') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill"
                                style="background-color: var(--color-primary); border-color: var(--color-primary);">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit & Delete Modals -->
@foreach($users as $user)
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department *</label>
                                <select name="department" class="form-select" required>
                                    <option value="sales" {{ $user->department == 'sales' ? 'selected' : '' }}>Sales</option>
                                    <option value="production" {{ $user->department == 'production' ? 'selected' : '' }}>Production</option>
                                    <option value="inventory" {{ $user->department == 'inventory' ? 'selected' : '' }}>Inventory</option>
                                    <option value="logistics" {{ $user->department == 'logistics' ? 'selected' : '' }}>Logistics</option>
                                    <option value="admin" {{ $user->department == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill"
                                    style="background-color: var(--color-primary); border-color: var(--color-primary);">
                                Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ $user->name }}</strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Success Toast & Auto Close Modal -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show position-fixed shadow-lg"
         style="top: 20px; right: 20px; z-index: 9999; min-width: 320px; border-radius: 12px;">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto close the Add User modal
            const modalEl = document.getElementById('addUserModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
            }

            // Auto dismiss toast after 5 seconds
            setTimeout(() => {
                const toast = document.querySelector('.alert-success');
                if (toast) {
                    toast.classList.remove('show');
                    toast.classList.add('fade');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        });
    </script>
@endif

<!-- Form Reset When Modal Closes -->
<script>
    const addUserModalEl = document.getElementById('addUserModal');
    if (addUserModalEl) {
        addUserModalEl.addEventListener('hidden.bs.modal', function () {
            addUserModalEl.querySelector('form').reset();
        });
    }
</script>

<!-- Tab Switching Script -->
<script>
    document.querySelectorAll('.admin-tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.admin-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

    document.getElementById('permission-user-select')?.addEventListener('change', function() {
        document.getElementById('selected-user-id').value = this.value;
    });
</script>
@endsection