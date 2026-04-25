<?php

namespace App\Http\Controllers;

use App\Ai\Agents\PokemonAgent;
use App\Ai\Agents\QuizAgent;
use App\Ai\Agents\QuizGenerator;
use App\Ai\Agents\SalesCoach;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Enums\Lab;

use function Laravel\Ai\agent;

/**
 * Controlador para ejemplos de uso de Gemma 3 con IA local (Ollama).
 */
class AgentsTestController extends Controller
{
    /**
     * Chat básico
     */
    public function chat(Request $request): JsonResponse
    {
        $mensaje = $request->input('mensaje', 'Hola, dime qué sabes sobre Laravel');

        $response = agent(
            instructions: 'Eres un asistente útil y conciso.',
        )->prompt(
            $mensaje,
            // model: 'gemma-3-12b-it-IQ4_XS'
            model: 'openai/gpt-oss-20b'
        );

        return response()->json([
            'respuesta' => $response->text,
            'modelo' => 'gemma3:12b-it-IQ4_XS (Ollama)',
        ]);
    }

    /**
     * Generación de código
     */
    public function generarCodigo(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo', 'migration de usuarios');

        $response = agent(
            instructions: 'Eres un experto en Laravel. Generas código limpio y sigues las mejores prácticas.',
        )->prompt(
            "Genera {$tipo} en Laravel 13. Solo dame el código, sin explicaciones.",
            // model: 'gemma-3-12b-it-IQ4_XS'
            model: 'openai/gpt-oss-20b'
        );

        return response()->json([
            'codigo' => $response->text,
            'solicitud' => $tipo,
        ]);
    }

    /**
     * Análisis de texto
     */
    public function analizar(Request $request): JsonResponse
    {
        $texto = $request->input('texto', 'Este producto es increíble, lo recomiendo totalmente');

        $response = agent(
            instructions: 'Eres un analizador de sentimientos. Respondes solo con positivo, negativo o neutro.',
        )->prompt(
            "Clasifica el siguiente texto: {$texto}",
            // model: 'gemma-3-12b-it-IQ4_XS'
            model: 'openai/gpt-oss-20b'
        );

        return response()->json([
            'sentimiento' => trim($response->text),
            'texto_original' => $texto,
        ]);
    }

    /**
     * Fallback entre proveedores
     */
    public function conFallback(Request $request): JsonResponse
    {
        $pregunta = $request->input('pregunta', 'Qué es Laravel?');

        $response = agent(
            instructions: 'Eres un asistente conciso.',
        )->prompt(
            $pregunta,
            provider: [Lab::Ollama, Lab::OpenAI],
            // model: 'gemma-3-12b-it-IQ4_XS'
            model: 'openai/gpt-oss-20b'
        );

        return response()->json([
            'respuesta' => $response->text,
            'proveedor' => 'Ollama con fallback a OpenAI',
        ]);
    }

    /**
     * Lista de Pokemon con PokemonAgent
     */
    public function listaPokemones(Request $request): JsonResponse
    {
        $resultado = (new PokemonAgent)->prompt(
            'Genera una lista de 3 Pokemon diferentes',
            // model: 'gemma-3-12b-it-IQ4_XS'
            // model: 'qwen3.5-27b'
            // model: 'text-embedding-nomic-embed-text-v1.5',
            model: 'Qwen3-4B-Instruct-2507-IQ4_XS',
            timeout: 120
        );

        // dd($resultado);
        return response()->json([
            'resultado' => $resultado->toArray(),
            'esquema' => [
                'pokemones' => [
                    '*' => [
                        'nombre' => 'string',
                        'tipo' => 'enum',
                        'tamano' => 'integer',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Quiz Verdadero/Falso basado en Posts
     *
     * Forma "pro": inyectar contenido al agente via withContext()
     *
     * Parámetros:
     * - ids: (opcional) IDs separados por coma ej "?ids=1,2,3"
     * - cantidad: (opcional) número de preguntas ej "?cantidad=5"
     */
    public function quizPosts(Request $request): JsonResponse
    {
        $ids = $request->input('ids');
        $cantidad = $request->input('cantidad', 5);

        $posts = Post::when($ids, fn ($query) => $query->whereIn('id', array_filter(explode(',', $ids))), fn ($query) => $query->where('posted', 'yes')->limit(1))
            ->get(['title', 'content']);

        if ($posts->isEmpty()) {
            return response()->json(['error' => 'No hay contenido'], 404);
        }

        // Instanciamos, pasamos el contexto y ejecutamos
        $resultado = (new QuizGenerator)
            ->withContext($posts)
            ->prompt(
                "Genera {$cantidad} preguntas de verdadero o falso sobre el material proporcionado.",
                // model: 'gemma-3-12b-it-IQ4_XS'
                model: 'openai/gpt-oss-20b'
            );

        return response()->json([
            'quiz' => $resultado->toArray(),
            'posts_utilizados' => $posts->count(),
        ]);
    }

    /**
     * Quiz con QuizAgent (versión simple)
     *
     * El contenido se pasa directamente en el prompt.
     */
    public function quizPostsSimple(Request $request): JsonResponse
    {
        $ids = $request->input('ids'); // 1,2,3
        $cantidad = $request->input('cantidad', 5);

        $posts = Post::when($ids, fn ($query) => $query->whereIn('id', array_filter(explode(',', $ids))), fn ($query) => $query->where('posted', 'yes')->limit(1))
            ->get(['title', 'content']);

        if ($posts->isEmpty()) {
            return response()->json(['error' => 'No hay contenido'], 404);
        }
        // dd( $posts);
        $contenido = $posts->map(fn ($post) => "Título: {$post->title}\nContenido: {$post->content}")->join("\n\n---\n\n");

        $prompt = "Basándote en los siguientes posts, genera {$cantidad} preguntas de verdadero o falso:\n\n{$contenido}";

        $resultado = (new QuizAgent)->prompt(
            $prompt,
            model: 'gemma-3-12b-it-IQ4_XS'
            // model: 'openai/gpt-oss-20b'
            // model: 'Qwen3-4B-Instruct-2507-IQ4_XS',
        );

        return response()->json([
            'quiz' => $resultado->toArray(),
            'posts_utilizados' => $posts->count(),
        ]);
    }

    public function salesCoach()
    {
        $response = (new SalesCoach)
            ->prompt('Analyze this sales transcript...',
                model: 'Qwen3-4B-Instruct-2507-IQ4_XS');

        return (string) $response;
    }
}
