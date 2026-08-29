<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Service class handling image optimization, WebP conversion, and thumbnail generation.
 */
class ImageOptimizer
{
    /** @var ImageManager The image driver manager instance */
    protected ImageManager $manager;

    /**
     * Create a new ImageOptimizer instance using the GD driver.
     */
    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(GdDriver::class);
    }

    /**
     * Convert any image source (path, UploadedFile, binary string, or Intervention image) to WebP and store it on the given disk.
     *
     * @param  UploadedFile|string|ImageInterface  $source  Source image to optimize
     * @param  string  $destinationPath  Target destination path on the storage disk
     * @param  string  $disk  Filesystem storage disk name
     * @param  int  $quality  WebP compression quality (0-100)
     * @param  int|null  $maxWidth  Optional maximum width constraint in pixels
     * @return string The stored file destination path
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
     * Convert an image to WebP and generate a square thumbnail variant.
     *
     * @param  UploadedFile|string|ImageInterface  $source  Source image to optimize
     * @param  string  $basePath  Base storage path prefix (e.g. "uploads/avatars/user_123")
     * @param  string  $disk  Filesystem storage disk name
     * @param  int  $quality  WebP compression quality (0-100)
     * @param  int|null  $maxWidth  Optional maximum width constraint for the main image
     * @param  int  $thumbSize  Square dimensions in pixels for the thumbnail
     * @return array{main: string, thumb: string} Associative array of generated storage paths
     */
    public function toWebpWithThumb(
        UploadedFile|string|ImageInterface $source,
        string $basePath,
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

    /**
     * Decode source into an Intervention Image instance.
     *
     * @param  UploadedFile|string|ImageInterface  $source  Input image source
     * @return ImageInterface The decoded image interface
     */
    private function resolveImage(UploadedFile|string|ImageInterface $source): ImageInterface
    {
        if ($source instanceof ImageInterface) {
            return $source;
        }

        return $this->manager->decode($source);
    }
}
