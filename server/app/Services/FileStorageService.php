<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    private const RESUME_DISK = 's3';
    private const LOGO_DISK = 's3';

    /**
     * @return array{storage_disk: string, storage_path: string, original_name: string, mime_type: string, size: int, checksum: string}
     */
    public function storeResume(UploadedFile $file, User $user): array
    {
        $disk = self::RESUME_DISK;
        $path = 'resumes/' . $user->id . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        Storage::disk($disk)->put($path, $file->getContent());

        return [
            'storage_disk' => $disk,
            'storage_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
        ];
    }

    /**
     * @return array{storage_disk: string, storage_path: string, original_name: string, mime_type: string, size: int, checksum: string}
     */
    public function storeLogo(UploadedFile $file, User $user): array
    {
        $disk = self::LOGO_DISK;
        $path = 'logos/' . $user->id . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        Storage::disk($disk)->put($path, $file->getContent());

        return [
            'storage_disk' => $disk,
            'storage_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
        ];
    }

    public function deleteFile(string $disk, string $path): void
    {
        Storage::disk($disk)->delete($path);
    }
}
