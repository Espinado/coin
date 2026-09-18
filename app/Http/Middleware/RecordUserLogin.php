<?php

namespace App\Http\Middleware;

use App\Services\UserLoginRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordUserLogin
{
    public function __construct(
        private readonly UserLoginRecorder $loginRecorder,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->loginRecorder->shouldRecord($request)) {
            $this->loginRecorder->record($request->user('web'), $request);
        }

        return $response;
    }
}
