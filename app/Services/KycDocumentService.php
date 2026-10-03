<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KycDocumentService
{
    public const MAX_FILES_PER_USER = 12;

    public const MAX_FILE_BYTES = 5_242_880; // 5 MB

    /**
     * @param  list<UploadedFile>  $files
     * @return list<KycDocument>
     */
    public function storeJpgs(User $user, array $files, ?Admin $admin = null): array
    {
        $files = array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));

        if ($files === []) {
            throw new RuntimeException(__('coin.admin.kyc_photos_required'));
        }

        return DB::transaction(function () use ($user, $files, $admin) {
            $existing = KycDocument::query()->where('user_id', $user->id)->count();

            if ($existing + count($files) > self::MAX_FILES_PER_USER) {
                throw new RuntimeException(__('coin.admin.kyc_photos_limit', [
                    'max' => self::MAX_FILES_PER_USER,
                ]));
            }

            $stored = [];

            foreach ($files as $file) {
                $this->assertJpg($file);

                $path = $file->store('kyc/'.$user->id, 'local');

                if ($path === false) {
                    throw new RuntimeException(__('coin.admin.kyc_photos_store_failed'));
                }

                $stored[] = KycDocument::query()->create([
                    'user_id' => $user->id,
                    'uploaded_by_admin_id' => $admin?->id,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => (int) $file->getSize(),
                ]);
            }

            if ($user->kyc_status === User::KYC_NONE) {
                $user->forceFill(['kyc_status' => User::KYC_PENDING])->save();
            }

            return $stored;
        });
    }

    public function delete(KycDocument $document): void
    {
        DB::transaction(function () use ($document) {
            Storage::disk($document->disk)->delete($document->path);
            $document->delete();
        });
    }

    public function stream(KycDocument $document): StreamedResponse
    {
        $disk = Storage::disk($document->disk);

        if (! $disk->exists($document->path)) {
            abort(404);
        }

        return $disk->response(
            $document->path,
            $document->original_name ?: 'kyc.jpg',
            [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline; filename="'.addslashes($document->original_name ?: 'kyc.jpg').'"',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    public function approve(User $user): User
    {
        $user->forceFill(['kyc_status' => User::KYC_APPROVED])->save();

        return $user->fresh();
    }

    private function assertJpg(UploadedFile $file): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mime = strtolower((string) ($file->getMimeType() ?: ''));

        if (! in_array($extension, ['jpg', 'jpeg'], true)) {
            throw new RuntimeException(__('coin.admin.kyc_photos_jpg_only'));
        }

        if (! in_array($mime, ['image/jpeg', 'image/jpg'], true)) {
            throw new RuntimeException(__('coin.admin.kyc_photos_jpg_only'));
        }

        if ($file->getSize() > self::MAX_FILE_BYTES) {
            throw new RuntimeException(__('coin.admin.kyc_photos_too_large'));
        }
    }
}
