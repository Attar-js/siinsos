<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memverifikasi API key untuk endpoint yang ditarik oleh sistem mitra.
 *
 * Mitra wajib mengirim header: X-API-KEY: <kunci-rahasia>
 * Kunci dibandingkan dengan nilai MITRA_API_KEY pada file .env.
 */
class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('app.mitra_api_key');
        $provided = $request->header('X-API-KEY');

        if (empty($expected) || ! is_string($provided) || ! hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak valid atau tidak diberikan.',
            ], 401);
        }

        return $next($request);
    }
}
