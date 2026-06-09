<?php

namespace App\Http\Controllers\Web;

use App\Enums\Locale;
use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, TranslationService $translations): RedirectResponse
    {
        $locale = $request->validate([
            'locale' => ['required', 'in:ar,en'],
        ])['locale'];

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        if ($request->user()) {
            $request->user()->update(['locale' => Locale::from($locale)]);
        }

        $translations->clearCache();

        return back();
    }
}
