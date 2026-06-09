<?php

namespace App\Http\Controllers\Web\Profile;

use App\Domain\HR\Models\EmployeeProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\TranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $profile = EmployeeProfile::where('user_id', $user->id)->first();

        return Inertia::render('Profile/Index', [
            'profile' => $profile,
            'roles' => $user->getRoleNames(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->update([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'phone' => $data['phone'] ?? null,
            'locale' => $data['locale'],
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $request->session()->put('locale', $data['locale']);
        app()->setLocale($data['locale']);
        app(TranslationService::class)->clearCache();

        return redirect()->back()->with('success', __('messages.updated'));
    }
}
