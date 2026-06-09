<?php

namespace App\Http\Controllers\Web\Settings;

use App\Support\DatabaseHelper;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->with('roles')
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('name_ar', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhere('name_en', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhere('email', DatabaseHelper::likeOperator(), "%{$search}%");
            }))
            ->orderBy('name_ar')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', __('settings.cannot_deactivate_self'));
        }

        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->back()->with('success', __('messages.updated'));
    }
}
