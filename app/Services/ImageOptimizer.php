<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class ImageOptimizer
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(GdDriver::class);
    }

    /**
     * Convert any image source (path, UploadedFile, binary string, or Intervention image)
     * to WebP and store it on the given disk.
     */
    public function toWebp(
        UploadedFile|string|ImageInterface $source,
        string $destinationPath,
        string $disk = 's3',
        int $quality = 80,
        ?int $maxWidth = null,
    ): string {
        $image = $this->resolveImage($source);

        if ($maxWidth && $image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $encoded = (string) $image->encodeUsingFormat(Format::WEBP, $quality);
        Storage::disk($disk)->put($destinationPath, $encoded);

        return $destinationPath;
    }

    /**
     * Same as toWebp but also generates a square thumbnail variant.
     * Returns ['main' => path, 'thumb' => path]
     */
    public function toWebpWithThumb(
        UploadedFile|string|ImageInterface $source,
        string $basePath, // e.g. "uploads/products/123"
        string $disk = 's3',
        int $quality = 80,
        ?int $maxWidth = 1200,
        int $thumbSize = 300,
    ): array {
        $image = $this->resolveImage($source);

        $main = clone $image;
        if ($maxWidth && $main->width() > $maxWidth) {
            $main->scale(width: $maxWidth);
        }

        $thumb = clone $image;
        $thumb->cover($thumbSize, $thumbSize);

        $mainPath = "{$basePath}.webp";
        $thumbPath = "{$basePath}_thumb.webp";

        Storage::disk($disk)->put($mainPath, (string) $main->encodeUsingFormat(Format::WEBP, $quality));
        Storage::disk($disk)->put($thumbPath, (string) $thumb->encodeUsingFormat(Format::WEBP, $quality));

        return ['main' => $mainPath, 'thumb' => $thumbPath];
    }

    private function resolveImage(UploadedFile|string|ImageInterface $source): ImageInterface
    {
        if ($source instanceof ImageInterface) {
            return $source;
        }

        return $this->manager->decode($source);
    }
}
