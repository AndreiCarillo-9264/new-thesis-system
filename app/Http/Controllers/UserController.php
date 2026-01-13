<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        $users = User::orderBy('name')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:50|unique:users,username',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'department'    => 'required|in:admin,sales,production,inventory,logistics',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name'       => $validated['name'],
            'username'   => $validated['username'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'department' => $validated['department'],
        ];

        // Handle profile photo (optional - using Laravel's default profile photo feature)
        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = '/storage/' . $path;
        }

        $user = User::create($data);

        ActivityLog::create([
            'user_id'   => auth()->id(),
            'action'    => 'created',
            'module'    => 'users',
            'record_id' => $user->id,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        // Prevent editing own department/role if paranoid, or allow
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:50|unique:users,username,' . $user->id,
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'department'    => 'required|in:admin,sales,production,inventory,logistics',
            'password'      => 'nullable|string|min:8|confirmed',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name'       => $validated['name'],
            'username'   => $validated['username'],
            'email'      => $validated['email'],
            'department' => $validated['department'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $data['profile_photo_path'] = '/storage/' . $path;
        }

        $user->update($data);

        ActivityLog::create([
            'user_id'   => auth()->id(),
            'action'    => 'updated',
            'module'    => 'users',
            'record_id' => $user->id,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself or the last admin (optional safety)
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        ActivityLog::create([
            'user_id'   => auth()->id(),
            'action'    => 'deleted',
            'module'    => 'users',
            'record_id' => $user->id,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User account deleted.');
    }
}