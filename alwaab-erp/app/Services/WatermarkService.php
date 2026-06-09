<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WatermarkService
{
    public function processAndStore(UploadedFile $file, User $employee, array $gps): array
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $directory = 'visits/'.now()->format('Y/m');

        $originalPath = $file->storeAs($directory, $filename, 'public');

        $watermarkText = [
            'employee' => $employee->name,
            'datetime' => now()->timezone('Asia/Dubai')->format('Y-m-d H:i:s'),
            'lat' => $gps['lat'] ?? null,
            'lng' => $gps['lng'] ?? null,
        ];

        // Watermark processing via Intervention Image can be added when package is installed
        $watermarkedPath = $originalPath;

        return [
            'original' => Storage::disk('public')->url($originalPath),
            'watermarked' => Storage::disk('public')->url($watermarkedPath),
            'watermark_text' => $watermarkText,
        ];
    }
}
