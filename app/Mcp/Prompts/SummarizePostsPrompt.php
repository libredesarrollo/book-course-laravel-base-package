<?php

namespace App\Mcp\Prompts;

use App\Models\Post;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Genera un prompt para resumir los posts más relevantes del blog.')]
class SummarizePostsPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     *
     * @return array<int, Response>
     */
    public function handle(Request $request): array
    {
        $tone = $request->string('tone')->toString() ?: 'neutral';
        $limit = (int) ($request->integer('limit') ?: 5);

        $titles = Post::query()
            ->published()
            ->latest()
            ->limit($limit)
            ->pluck('title')
            ->implode(', ');

        return [
            Response::text("Eres un redactor experto. Resume los siguientes posts del blog en un tono {$tone}.")->asAssistant(),
            Response::text("Posts a resumir: [{$titles}]"),
        ];
    }

    /**
     * Get the prompt's arguments.
     *
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'tone',
                description: 'El tono del resumen (formal, casual, técnico, etc.). Por defecto "neutral".',
                required: false,
            ),
            new Argument(
                name: 'limit',
                description: 'Cantidad de posts a considerar. Por defecto 5.',
                required: false,
            ),
        ];
    }
}
