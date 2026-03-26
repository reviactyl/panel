<?php

namespace App\Http\Controllers\Base;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class PwaManifestController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name'),
            'short_name' => config('app.name'),
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => config('designify.color800'),
            'theme_color' => config('designify.colorPrimary'),
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => asset('favicons/web-app-manifest-192x192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => asset('favicons/web-app-manifest-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ]);
    }
}
