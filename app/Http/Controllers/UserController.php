<?php

namespace App\Http\Controllers;

use App\Actions\User\ToggleUserActiveStatusAction;
use App\Enums\Role;
use App\Http\Requests\User\IndexRequest;
use App\Http\Requests\User\ShowRequest;
use App\Http\Requests\User\ToggleRequest;
use App\Http\Resources\AdminResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use HindBiswas\ModelUtils\Utils\EnumUtil;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(IndexRequest $request)
    {
        $filters = $request->validated();

        $filters['search'] = $filters['search'] ?? null;
        $filters['is_active'] = $filters['is_active'] ?? null;
        $filters['role'] = $filters['role'] ?? null;

        $super = User::super();

        $users = User::filter($filters)
            ->where('id', '!=', $super?->id)
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.feature.pagination'))
            ->withQueryString();

        return inertia('Users/Index', [
            'list' => Inertia::defer(fn () => UserResource::collection($users)),
            'filters' => $filters,
            'roleOptions' => EnumUtil::toOptions(Role::class),
        ]);
    }

    public function show(ShowRequest $request, User $user)
    {
        $currentUser = Auth::user();
        if ($user->id === $currentUser->id) {
            return redirect()->route('profile.show');
        }

        $roles = $user->getRoleNames();
        $roleAccount = $user && count($roles) ? match ($roles[0]) {
            Role::ADMIN->value => AdminResource::make($user->admin),
            default => null,
        } : null;

        return inertia('Users/Show', [
            'user' => new UserResource($user),
            'roleAccount' => $roleAccount,
        ]);
    }

    public function toggle(User $user, ToggleRequest $request, ToggleUserActiveStatusAction $toggleUserActiveStatusAction)
    {
        $toggleUserActiveStatusAction->handle($user);
    }
}
