<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\Yaml\Yaml;

class OpenApiDocumentationController extends Controller
{
    public function ui(): View
    {
        abort_unless(config('openapi.ui_enabled'), 404);

        return view('openapi.documentation', [
            'specUrl' => url(config('openapi.spec_url_path')),
        ]);
    }

    public function specYaml(): Response
    {
        abort_unless(config('openapi.ui_enabled'), 404);

        $path = config('openapi.spec_path');
        abort_unless(is_string($path) && File::isFile($path), 404);

        return response(File::get($path), 200, [
            'Content-Type' => 'application/yaml; charset=utf-8',
        ]);
    }

    public function specJson(): JsonResponse
    {
        abort_unless(config('openapi.ui_enabled'), 404);

        $path = config('openapi.spec_path');
        abort_unless(is_string($path) && File::isFile($path), 404);

        /** @var array<string, mixed> $document */
        $document = Yaml::parse(File::get($path));

        return response()->json($document);
    }
}
