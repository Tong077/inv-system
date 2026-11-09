<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageHandleService
{
    public function uploadImage(?UploadedFile $image, string $directory = 'images', string $disk = 'public'): ?string
    {
        if (!$image) {
            return null;
        }

        return $image->store($directory, $disk) ?: null;
    }

    public function updateImage(?UploadedFile $newImage, ?string $oldImagePath, string $directory = 'images', string $disk = 'public'): ?string
    {
        if (!$newImage) {
            return $oldImagePath;
        }

        $newImagePath = $this->uploadImage($newImage, $directory, $disk);

        if ($newImagePath && $oldImagePath) {
            $this->deleteImage($oldImagePath, $disk);
        }

        return $newImagePath ?? $oldImagePath;
    }

    public function deleteImage(?string $imagePath, string $disk = 'public'): bool
    {
        if (!$imagePath) {
            return false;
        }

        if (!Storage::disk($disk)->exists($imagePath)) {
            return false;
        }

        return Storage::disk($disk)->delete($imagePath);
    }
}
