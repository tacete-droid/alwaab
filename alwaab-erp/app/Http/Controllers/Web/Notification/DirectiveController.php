<?php

namespace App\Http\Controllers\Web\Notification;

use App\Domain\Notifications\Models\SystemDirective;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\StoreDirectiveRequest;
use App\Models\User;
use App\Services\AppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DirectiveController extends Controller
{
    public function __construct(private AppNotificationService $notifications) {}

    public function index(Request $request): Response
    {
        $directives = SystemDirective::query()
            ->with('creator:id,name_ar,name_en')
            ->latest()
            ->paginate(20);

        return Inertia::render('Directives/Index', [
            'directives' => $directives,
            'roles' => Role::orderBy('name')->pluck('name'),
            'users' => User::where('is_active', true)->orderBy('name_ar')->get(['id', 'name_ar', 'name_en']),
        ]);
    }

    public function store(StoreDirectiveRequest $request): RedirectResponse
    {
        $directive = SystemDirective::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $count = $this->notifications->sendDirective($directive);

        return redirect()->back()->with('success', __('notifications.directive_sent', ['count' => $count]));
    }
}
