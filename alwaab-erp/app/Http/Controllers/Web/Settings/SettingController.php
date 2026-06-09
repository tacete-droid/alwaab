<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(private SettingsService $settingsService) {}

    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'settings' => $this->settingsService->companySettings(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['low_stock_alert'] = $request->boolean('low_stock_alert') ? '1' : '0';

        $this->settingsService->setMany($data);

        return redirect()->back()->with('success', __('messages.updated'));
    }
}
