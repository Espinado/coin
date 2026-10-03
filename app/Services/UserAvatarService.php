<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserAvatarService
{
    public const DISK = 'local';

    public const MAX_FILE_BYTES = 2_097_152; // 2 MB

    public const SIZE_PX = 256;

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /** @var list<string> */
    public const ALLOWED_MIMES = ['image/jpeg', 'image/jpg', 'image/png'];

    public function store(User $user, UploadedFile $file): User
    {
        $this->assertImage($file);

        if (! extension_loaded('gd')) {
            throw new RuntimeException(__('coin.profile.avatar_store_failed'));
        }

        $jpeg = $this->makeSquareJpeg($file);

        return DB::transaction(function () use ($user, $jpeg) {
            $disk = Storage::disk(self::DISK);
            $directory = 'avatars/'.$user->id;
            $path = $directory.'/avatar-'.str_replace('.', '', uniqid('', true)).'.jpg';

            $disk->makeDirectory($directory);

            foreach ($disk->files($directory) as $existing) {
                $disk->delete($existing);
            }

            if (! $disk->put($path, $jpeg)) {
                throw new RuntimeException(__('coin.profile.avatar_store_failed'));
            }

            $user->forceFill([
                'avatar_path' => $path,
                'updated_at' => now(),
            ])->save();

            return $user->fresh();
        });
    }

    public function delete(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $disk = Storage::disk(self::DISK);
            $directory = 'avatars/'.$user->id;

            if ($disk->exists($directory)) {
                foreach ($disk->files($directory) as $existing) {
                    $disk->delete($existing);
                }
            } elseif (filled($user->avatar_path)) {
                $disk->delete($user->avatar_path);
            }

            $user->forceFill([
                'avatar_path' => null,
                'updated_at' => now(),
            ])->save();

            return $user->fresh();
        });
    }

    public function stream(User $user): StreamedResponse
    {
        abort_unless($user->hasAvatar(), 404);

        $disk = Storage::disk(self::DISK);
        $path = (string) $user->avatar_path;

        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, 'avatar.jpg', [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="avatar.jpg"',
            'Cache-Control' => 'private, no-cache, must-revalidate',
        ]);
    }

    private function makeSquareJpeg(UploadedFile $file): string
    {
        $binary = file_get_contents($file->getRealPath() ?: '');

        if ($binary === false || $binary === '') {
            throw new RuntimeException(__('coin.profile.avatar_store_failed'));
        }

        $source = @imagecreatefromstring($binary);

        if ($source === false) {
            throw new RuntimeException(__('coin.profile.avatar_format'));
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width < 1 || $height < 1) {
            imagedestroy($source);
            throw new RuntimeException(__('coin.profile.avatar_format'));
        }

        $side = min($width, $height);
        $srcX = (int) floor(($width - $side) / 2);
        $srcY = (int) floor(($height - $side) / 2);

        $canvas = imagecreatetruecolor(self::SIZE_PX, self::SIZE_PX);

        if ($canvas === false) {
            imagedestroy($source);
            throw new RuntimeException(__('coin.profile.avatar_store_failed'));
        }

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            self::SIZE_PX,
            self::SIZE_PX,
            $side,
            $side,
        );

        ob_start();
        imagejpeg($canvas, null, 85);
        $jpeg = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if (! is_string($jpeg) || $jpeg === '') {
            throw new RuntimeException(__('coin.profile.avatar_store_failed'));
        }

        return $jpeg;
    }

    private function assertImage(UploadedFile $file): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mime = strtolower((string) ($file->getMimeType() ?: ''));

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException(__('coin.profile.avatar_format'));
        }

        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new RuntimeException(__('coin.profile.avatar_format'));
        }

        if ($file->getSize() > self::MAX_FILE_BYTES) {
            throw new RuntimeException(__('coin.profile.avatar_too_large'));
        }
    }
}
