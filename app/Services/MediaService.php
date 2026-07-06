<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    const MAX_WIDTH  = 1920;
    const MAX_HEIGHT = 1200;
    const QUALITY    = 85;

    // ──────────────────────────────────────────────────────────────────────────
    // Upload entry point (UploadedFile o TemporaryUploadedFile)
    // ──────────────────────────────────────────────────────────────────────────

    public static function upload(UploadedFile $file, ?string $folder = null): Media
    {
        $mime   = $file->getMimeType() ?? $file->getClientMimeType();
        $type   = self::detectType($mime);
        $folder = $folder ?? ('media/' . now()->format('Y/m'));

        return $type === 'image'
            ? self::processImage($file, $folder)
            : self::storeFile($file, $folder, $type);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Re-process an already-stored file (path on 'public' disk)
    // Used when Filament FileUpload already saved the file before we can hook in
    // ──────────────────────────────────────────────────────────────────────────

    public static function processFromStoredPath(string $storedPath): Media
    {
        $fullPath = Storage::disk('public')->path($storedPath);
        $mime     = mime_content_type($fullPath) ?: 'application/octet-stream';
        $type     = self::detectType($mime);

        if ($type === 'image') {
            $img = Image::read($fullPath);

            if ($img->width() > self::MAX_WIDTH) {
                $img->scaleDown(width: self::MAX_WIDTH);
            }

            $baseName = Str::slug(pathinfo($storedPath, PATHINFO_FILENAME));
            $webpName = $baseName . '-' . substr(uniqid(), -6) . '.webp';
            $webpPath = dirname($storedPath) . '/' . $webpName;

            $encoded = $img->toWebp(quality: self::QUALITY);
            Storage::disk('public')->put($webpPath, (string) $encoded);

            // Eliminar original si el nombre cambió
            if ($webpPath !== $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            return Media::create([
                'name'      => $baseName . '.webp',
                'file_name' => $webpName,
                'disk'      => 'public',
                'path'      => $webpPath,
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'size'      => Storage::disk('public')->size($webpPath),
                'width'     => $img->width(),
                'height'    => $img->height(),
                'type'      => 'image',
            ]);
        }

        // Non-image: just register
        return Media::create([
            'name'      => basename($storedPath),
            'file_name' => basename($storedPath),
            'disk'      => 'public',
            'path'      => $storedPath,
            'mime_type' => $mime,
            'extension' => pathinfo($storedPath, PATHINFO_EXTENSION),
            'size'      => Storage::disk('public')->size($storedPath),
            'type'      => $type,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internals
    // ──────────────────────────────────────────────────────────────────────────

    protected static function processImage(UploadedFile $file, string $folder): Media
    {
        $img = Image::read($file->getPathname());

        if ($img->width() > self::MAX_WIDTH) {
            $img->scaleDown(width: self::MAX_WIDTH);
        }

        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $baseName . '-' . substr(uniqid(), -6) . '.webp';
        $path     = $folder . '/' . $fileName;

        $encoded = $img->toWebp(quality: self::QUALITY);
        Storage::disk('public')->put($path, (string) $encoded);

        return Media::create([
            'name'      => $file->getClientOriginalName(),
            'file_name' => $fileName,
            'disk'      => 'public',
            'path'      => $path,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size'      => Storage::disk('public')->size($path),
            'width'     => $img->width(),
            'height'    => $img->height(),
            'type'      => 'image',
        ]);
    }

    protected static function storeFile(UploadedFile $file, string $folder, string $type): Media
    {
        $ext      = $file->getClientOriginalExtension();
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $baseName . '-' . substr(uniqid(), -6) . '.' . $ext;
        $path     = $file->storeAs($folder, $fileName, 'public');

        return Media::create([
            'name'      => $file->getClientOriginalName(),
            'file_name' => $fileName,
            'disk'      => 'public',
            'path'      => $path,
            'mime_type' => $file->getMimeType(),
            'extension' => $ext,
            'size'      => $file->getSize(),
            'type'      => $type,
        ]);
    }

    public static function detectType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (in_array($mime, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])) return 'document';
        return 'other';
    }
}
