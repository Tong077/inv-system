<?php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class ImageHandleService
{
    public function uploadImage(?UploadedFile $image, string $directory = 'images', string $disk = 'public'): ?string
    {
        if (!$image) {
            return null;
        }
        return $image->store($directory, $disk);
        // if ($image) {
        //     return $image->store($directory, $disk);

        // } else {
        //     return null;
        // }
    }
    public function updateImage(?UploadedFile $newImage, ?string $oldImagePath, string $directory = 'images', string $disk = 'public'): ?string
    {
        if (!$newImage)
            return $oldImagePath;
        $newImage = $this->updateImage($newImage, $directory, $disk);
        if ($oldImagePath && $newImage) {
            $this->deleteImage($oldImagePath, $disk);
        }
        return $newImage;
    }
    public function deleteImage(?string $imagePath, string $disk = 'public'): bool
    {
        if (!$imagePath) {
            return false;
        }
        if (Storage::disk($disk)->exists($imagePath)) {
            return false;
        }
        return Storage::disk($disk)->delete($imagePath);
    }
}
