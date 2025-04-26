<?php

namespace App\Http\Controllers;

use App\Data\UserData;
use App\Enums\Permissions\UserPermissions;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Interfaces\UserServiceInterface;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use Spatie\LaravelData\PaginatedDataCollection;

class UserController extends Controller implements HasMiddleware
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public static function middleware(): array
    {
        return [
            checkPermission(UserPermissions::VIEW_USER, only: ['index', 'show']),
            checkPermission(UserPermissions::CREATE_USER, only: ['create', 'store']),
            checkPermission(UserPermissions::UPDATE_USER, only: ['edit', 'update']),
            checkPermission(UserPermissions::DELETE_USER, only: ['destroy']),
        ];
    }

    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => $this->userService->getAllUsers()
        ]);
    }

    public function show(User $user)
    {
        return Inertia::render('Users/Show', [
            'user' => UserData::from($this->userService->getUserById($user->id))
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all()
        ]);
    }

    public function store(UserStoreRequest $request)
    {
        try {
            $this->userService->createUser($request->validated());
            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => UserData::from($this->userService->getUserById($user->id)),
            'roles' => Role::all()
        ]);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $this->userService->updateUser($user, $request->validated());
            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            $this->userService->deleteUser($user);
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }
}
