<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CspReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        Log::channel('csp')->warning('CSP violation', [
            'report' => Str::limit((string) $request->getContent(), 2000),
            'ua' => Str::limit((string) $request->userAgent(), 200),
        ]);

        return response()->noContent();
    }
}
