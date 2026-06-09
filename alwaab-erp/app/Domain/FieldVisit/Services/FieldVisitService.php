<?php

namespace App\Domain\FieldVisit\Services;

use App\Domain\FieldVisit\Models\FieldVisit;
use App\Domain\FieldVisit\Models\VisitPhoto;
use App\Enums\FieldVisitStatus;
use App\Models\User;
use App\Services\WatermarkService;
use Illuminate\Http\UploadedFile;

class FieldVisitService
{
    public function __construct(private WatermarkService $watermarkService) {}

    public function startVisit(array $data, User $employee): FieldVisit
    {
        return FieldVisit::create([
            ...$data,
            'employee_id' => $employee->id,
            'visited_at' => now(),
            'status' => FieldVisitStatus::InProgress,
        ]);
    }

    public function updateLocation(FieldVisit $visit, float $lat, float $lng, User $employee): FieldVisit
    {
        abort_unless($visit->employee_id === $employee->id, 403);
        abort_unless($visit->status === FieldVisitStatus::InProgress, 422);

        $visit->update(['lat' => $lat, 'lng' => $lng]);

        return $visit->fresh(['project', 'employee', 'photos']);
    }

    public function completeVisit(FieldVisit $visit, ?string $notes = null): FieldVisit
    {
        $visit->update([
            'status' => FieldVisitStatus::Completed,
            'completed_at' => now(),
            'notes' => $notes ?? $visit->notes,
        ]);

        return $visit->fresh(['project', 'employee', 'photos']);
    }

    public function uploadPhoto(FieldVisit $visit, UploadedFile $file, array $gps, User $employee): VisitPhoto
    {
        $paths = $this->watermarkService->processAndStore($file, $employee, $gps);

        return VisitPhoto::create([
            'visit_id' => $visit->id,
            'original_url' => $paths['original'],
            'watermarked_url' => $paths['watermarked'],
            'lat' => $gps['lat'] ?? null,
            'lng' => $gps['lng'] ?? null,
            'taken_at' => now(),
            'watermark_text' => $paths['watermark_text'],
        ]);
    }
}
