<?php

namespace App\Mcp\Tools;

use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Lista los posts del blog con paginación y filtro opcional por texto (título o contenido).')]
class ListPostsTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ], [
            'per_page.min' => 'per_page debe ser al menos 1.',
            'per_page.max' => 'per_page no puede ser mayor a 50.',
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Post::query()->latest();

        if ($search = $validated['search'] ?? null) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate($perPage);

        if ($posts->isEmpty()) {
            return Response::text('No se encontraron posts.');
        }

        $summary = $posts->getCollection()->map(fn (Post $post) => [
            'id' => $post->id,
            'title' => $post->title,
            'posted' => $post->isPublished(),
            'created_at' => $post->created_at?->toDateTimeString(),
        ])->all();

        $header = sprintf(
            'Mostrando %d de %d posts (página %d de %d).',
            $posts->count(),
            $posts->total(),
            $posts->currentPage(),
            $posts->lastPage(),
        );

        return Response::structured([
            'summary' => $header,
            'total' => $posts->total(),
            'page' => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
            'posts' => $summary,
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()
                ->description('Texto a buscar en el título o contenido. Opcional.'),
            'per_page' => $schema->integer()
                ->description('Cantidad de posts por página (1-50). Por defecto 10.'),
        ];
    }
}
