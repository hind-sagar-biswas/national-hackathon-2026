<?php

namespace App\Models\Traits;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Jetstream\HasProfilePhoto as JetstreamHasProfilePhoto;

/**
 * @property string|null $profile_photo_path
 *
 * @method $this forceFill(array $attributes)
 * @method bool save(array $options = [])
 */
trait HasProfilePhoto
{
    use JetstreamHasProfilePhoto;

    /**
     * Override: Update the user's profile photo using WebP optimization.
     *
     * @param  string  $storagePath
     * @return void
     */
    public function updateProfilePhoto(UploadedFile $photo, $storagePath = 'profile-photos')
    {
        tap($this->profile_photo_path, function ($previous) use ($photo, $storagePath) {
            $optimizer = app(ImageOptimizer::class);

            $disk = $this->profilePhotoDisk();
            $filename = Str::random(40).'.webp';
            $destinationPath = trim($storagePath, '/').'/'.$filename;

            // Process and upload
            $path = $optimizer->toWebp(
                source: $photo,
                destinationPath: $destinationPath,
                disk: $disk,
                quality: 85,
                maxWidth: 500
            );

            // Save the new path
            $this->forceFill([
                'profile_photo_path' => $path,
            ])->save();

            // Delete the old photo if it exists
            if ($previous) {
                Storage::disk($disk)->delete($previous);
            }
        });
    }
}
