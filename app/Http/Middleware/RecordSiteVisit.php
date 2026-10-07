<?php

namespace App\Http\Middleware;

use App\Services\SiteVisitRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordSiteVisit
{
    public function __construct(
        private readonly SiteVisitRecorder $visits,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->visits->shouldRecord($request)) {
            $this->visits->record($request);
        }

        return $response;
    }
}
