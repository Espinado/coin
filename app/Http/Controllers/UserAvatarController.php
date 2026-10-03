<?php

namespace App\Http\Controllers;

use App\Services\UserAvatarService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserAvatarController extends Controller
{
    public function show(Request $request, UserAvatarService $avatars): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 404);

        return $avatars->stream($user);
    }
}
