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

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /** @var list<string> */
    public const ALLOWED_MIMES = ['image/jpeg', 'image/jpg', 'image/png'];

    public function store(User $user, UploadedFile $file): User
    {
        $this->assertImage($file);

        return DB::transaction(function () use ($user, $file) {
            $disk = Storage::disk(self::DISK);
            $directory = 'avatars/'.$user->id;
            $extension = strtolower((string) $file->getClientOriginalExtension());
            $extension = $extension === 'jpeg' ? 'jpg' : $extension;
            $path = $directory.'/avatar.'.$extension;

            if (filled($user->avatar_path) && $user->avatar_path !== $path) {
                $disk->delete($user->avatar_path);
            }

            $disk->makeDirectory($directory);

            if (! $disk->putFileAs($directory, $file, 'avatar.'.$extension)) {
                throw new RuntimeException(__('coin.profile.avatar_store_failed'));
            }

            $user->forceFill(['avatar_path' => $path])->save();

            return $user->fresh();
        });
    }

    public function delete(User $user): User
    {
        return DB::transaction(function () use ($user) {
            if (filled($user->avatar_path)) {
                Storage::disk(self::DISK)->delete($user->avatar_path);
            }

            $user->forceFill(['avatar_path' => null])->save();

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

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $contentType = $extension === 'png' ? 'image/png' : 'image/jpeg';

        return $disk->response($path, 'avatar.'.$extension, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="avatar.'.$extension.'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
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
