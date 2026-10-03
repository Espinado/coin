<?php

namespace App\Http\Controllers;

use App\Models\SupportMessageAttachment;
use App\Services\SupportMessageAttachmentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportAttachmentController extends Controller
{
    public function show(
        Request $request,
        SupportMessageAttachment $attachment,
        SupportMessageAttachmentService $attachments,
    ): StreamedResponse {
        $attachment->loadMissing('message.ticket');

        $ticket = $attachment->message?->ticket;
        $user = $request->user();

        abort_unless($ticket !== null && $user !== null, 404);
        abort_unless((int) $ticket->user_id === (int) $user->id, 403);

        return $attachments->stream($attachment);
    }
}
