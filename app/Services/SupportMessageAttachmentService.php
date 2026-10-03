<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\KycDocument;
use App\Models\SupportMessageAttachment;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportMessageAttachmentService
{
    public const MAX_FILES_PER_MESSAGE = 5;

    public const MAX_FILE_BYTES = 5_242_880;

    /**
     * @param  list<UploadedFile>  $files
     * @return list<SupportMessageAttachment>
     */
    public function storeJpgsForMessage(SupportTicketMessage $message, array $files): array
    {
        $files = array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));

        if ($files === []) {
            return [];
        }

        if (count($files) > self::MAX_FILES_PER_MESSAGE) {
            throw new RuntimeException(__('coin.support.attachments_limit', [
                'max' => self::MAX_FILES_PER_MESSAGE,
            ]));
        }

        $stored = [];

        foreach ($files as $file) {
            $this->assertJpg($file);

            $path = $file->store('support/'.$message->support_ticket_id.'/'.$message->id, 'local');

            if ($path === false) {
                throw new RuntimeException(__('coin.support.attachments_store_failed'));
            }

            $stored[] = SupportMessageAttachment::query()->create([
                'support_ticket_message_id' => $message->id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => (int) $file->getSize(),
            ]);
        }

        return $stored;
    }

    public function stream(SupportMessageAttachment $attachment): StreamedResponse
    {
        $disk = Storage::disk($attachment->disk);

        if (! $disk->exists($attachment->path)) {
            abort(404);
        }

        return $disk->response(
            $attachment->path,
            $attachment->original_name ?: 'attachment.jpg',
            [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline; filename="'.addslashes($attachment->original_name ?: 'attachment.jpg').'"',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    public function saveToKyc(SupportMessageAttachment $attachment, Admin $admin): KycDocument
    {
        $attachment->loadMissing('message.ticket.user', 'kycDocument');

        if ($attachment->isSavedToKyc() && $attachment->kycDocument) {
            return $attachment->kycDocument;
        }

        $ticket = $attachment->message?->ticket;
        $user = $ticket?->user;

        if (! $user instanceof User || $ticket?->isGuest()) {
            throw new RuntimeException(__('coin.admin.support_attachment_kyc_guest'));
        }

        return DB::transaction(function () use ($attachment, $admin, $user) {
            $disk = Storage::disk($attachment->disk);

            if (! $disk->exists($attachment->path)) {
                throw new RuntimeException(__('coin.admin.support_attachment_missing'));
            }

            $existing = KycDocument::query()->where('user_id', $user->id)->count();

            if ($existing >= KycDocumentService::MAX_FILES_PER_USER) {
                throw new RuntimeException(__('coin.admin.kyc_photos_limit', [
                    'max' => KycDocumentService::MAX_FILES_PER_USER,
                ]));
            }

            $targetPath = 'kyc/'.$user->id.'/'.basename($attachment->path);

            if (! $disk->copy($attachment->path, $targetPath)) {
                // Some drivers treat copy as false when destination exists; overwrite then.
                $disk->put($targetPath, $disk->get($attachment->path));
            }

            $document = KycDocument::query()->create([
                'user_id' => $user->id,
                'uploaded_by_admin_id' => $admin->id,
                'disk' => $attachment->disk,
                'path' => $targetPath,
                'original_name' => $attachment->original_name,
                'size' => $attachment->size,
            ]);

            $attachment->update(['kyc_document_id' => $document->id]);

            if ($user->kyc_status === User::KYC_NONE) {
                $user->forceFill(['kyc_status' => User::KYC_PENDING])->save();
            }

            return $document;
        });
    }

    /** @return array<string, mixed> */
    public function toBroadcastArray(SupportMessageAttachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'original_name' => $attachment->original_name,
            'saved_to_kyc' => $attachment->isSavedToKyc(),
            'url' => route('support.attachments.show', $attachment),
            'admin_url' => route('admin.support.attachments.show', $attachment),
            'save_to_kyc_url' => route('admin.support.attachments.save-kyc', $attachment),
        ];
    }

    private function assertJpg(UploadedFile $file): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mime = strtolower((string) ($file->getMimeType() ?: ''));

        if (! in_array($extension, ['jpg', 'jpeg'], true)) {
            throw new RuntimeException(__('coin.support.attachments_jpg_only'));
        }

        if (! in_array($mime, ['image/jpeg', 'image/jpg'], true)) {
            throw new RuntimeException(__('coin.support.attachments_jpg_only'));
        }

        if ($file->getSize() > self::MAX_FILE_BYTES) {
            throw new RuntimeException(__('coin.support.attachments_too_large'));
        }
    }
}
