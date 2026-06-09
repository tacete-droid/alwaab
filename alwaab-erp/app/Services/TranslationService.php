<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class TranslationService
{
    public function forFrontend(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        return Cache::remember("frontend.translations.{$locale}", 3600, function () use ($locale) {
            $path = lang_path($locale);
            $translations = [];

            if (! File::isDirectory($path)) {
                return $translations;
            }

            foreach (File::files($path) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $group = $file->getFilenameWithoutExtension();
                $translations[$group] = require $file->getPathname();
            }

            return $translations;
        });
    }

    public function clearCache(): void
    {
        Cache::forget('frontend.translations.ar');
        Cache::forget('frontend.translations.en');
    }
}
