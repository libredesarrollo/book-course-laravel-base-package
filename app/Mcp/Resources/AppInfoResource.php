<?php

namespace App\Mcp\Resources;

use App\Models\Post;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Description('Información general de la aplicación: nombre, entorno y estadísticas del blog.')]
#[Uri('app://info')]
#[MimeType('application/json')]
class AppInfoResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        return Response::json([
            'name' => config('app.name'),
            'environment' => config('app.env'),
            'locale' => config('app.locale'),
            'stats' => [
                'posts' => Post::count(),
                'published_posts' => Post::published()->count(),
                'users' => User::count(),
            ],
        ]);
    }
}
