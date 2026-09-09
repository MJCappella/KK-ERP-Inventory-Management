<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class DocsController extends Controller
{
    /**
     * Display the Swagger UI documentation interface.
     */
    public function index(): View
    {
        return view('docs.swagger');
    }

    /**
     * Serve the raw OpenAPI 3.0 specification.
     */
    public function spec(): Response
    {
        $path = resource_path('docs/openapi.yaml');

        if (! file_exists($path)) {
            abort(404, 'OpenAPI specification file not found.');
        }

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/yaml; charset=utf-8',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
