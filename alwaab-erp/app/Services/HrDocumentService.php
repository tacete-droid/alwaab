<?php

namespace App\Services;

use App\Domain\HR\Models\EmployeeProfile;
use App\Enums\HrDocumentType;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HrDocumentService
{
    public const COLLECTION = 'hr_documents';

    public function list(EmployeeProfile $employee): array
    {
        return $employee->getMedia(self::COLLECTION)
            ->map(fn (Media $media) => $this->format($media))
            ->values()
            ->all();
    }

    public function upload(
        EmployeeProfile $employee,
        UploadedFile $file,
        HrDocumentType $type,
        ?string $title = null,
        ?string $expiresAt = null,
    ): array {
        $media = $employee
            ->addMedia($file)
            ->withCustomProperties([
                'document_type' => $type->value,
                'title' => $title ?: $file->getClientOriginalName(),
                'expires_at' => $expiresAt,
            ])
            ->toMediaCollection(self::COLLECTION);

        return $this->format($media);
    }

    public function delete(EmployeeProfile $employee, string $mediaId): void
    {
        $media = $employee->getMedia(self::COLLECTION)->firstWhere('uuid', $mediaId)
            ?? $employee->getMedia(self::COLLECTION)->firstWhere('id', $mediaId);

        if ($media) {
            $media->delete();
        }
    }

    public function format(Media $media): array
    {
        return [
            'id' => $media->uuid,
            'name' => $media->file_name,
            'title' => $media->getCustomProperty('title', $media->file_name),
            'document_type' => $media->getCustomProperty('document_type', 'other'),
            'expires_at' => $media->getCustomProperty('expires_at'),
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'url' => $media->getUrl(),
            'uploaded_at' => $media->created_at?->toIso8601String(),
        ];
    }
}
