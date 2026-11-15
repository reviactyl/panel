<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EggImageController extends Controller
{
    public function __invoke(Request $request, Egg $egg, string $version): Response
    {
        abort_unless(hash_equals(route('egg-image', ['egg' => $egg->uuid, 'version' => $version], false), $egg->image ?? ''), 404);
        $image = DB::table('egg_images')->where('egg_id', $egg->id)->value('data') ?? '';
        abort_unless(preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,/', $image, $matches), 404);
        $contents = base64_decode(substr($image, strlen($matches[0])), true);
        abort_if($contents === false, 404);

        $response = response($contents, headers: [
            'Content-Type' => $matches[1],
            'Cache-Control' => 'private, max-age=31536000, immutable',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'X-Content-Type-Options' => 'nosniff',
        ])->setEtag($version);
        $response->isNotModified($request);

        return $response;
    }
}
