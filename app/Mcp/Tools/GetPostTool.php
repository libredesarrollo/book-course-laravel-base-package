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

#[Description('Obtiene el contenido completo de un post del blog a partir de su ID.')]
class GetPostTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ], [
            'id.required' => 'Debes indicar el ID del post.',
            'id.integer' => 'El ID del post debe ser un número entero.',
        ]);

        $post = Post::find($validated['id']);

        if (! $post) {
            return Response::error("El post con ID {$validated['id']} no existe.");
        }

        return Response::structured([
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'description' => $post->description,
            'content' => $post->content,
            'category_id' => $post->category_id,
            'user_id' => $post->user_id,
            'posted' => $post->isPublished(),
            'created_at' => $post->created_at?->toDateTimeString(),
            'updated_at' => $post->updated_at?->toDateTimeString(),
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
            'id' => $schema->integer()
                ->description('El ID numérico del post a obtener.')
                ->required(),
        ];
    }
}
