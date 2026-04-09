<?php

namespace App\Http\Controllers;

use App\Ai\Agents\PokemonAgent;
use App\Ai\Agents\QuizAgent;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Enums\Lab;

use function Laravel\Ai\agent;

/**
 * Controlador para ejemplos de uso de Gemma 3 con IA local (Ollama).
 */
class GemmaController extends Controller
{
    /**
     * Chat básico con Gemma 3
     */
    public function chat(Request $request): JsonResponse
    {
        $mensaje = $request->input('mensaje', 'Hola, dime qué sabes sobre Laravel');

        $response = agent(
            instructions: 'Eres un asistente útil y conciso.',
        )->prompt(
            $mensaje,
            model: 'gemma-3-12b-it-IQ4_XS'
        );

        return response()->json([
            'respuesta' => $response->text,
            'modelo' => 'gemma3:12b-it-IQ4_XS (Ollama)',
        ]);
    }

    /**
     * Generación de código con Gemma 3
     */
    public function generarCodigo(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo', 'migration de usuarios');

        $response = agent(
            instructions: 'Eres un experto en Laravel. Generas código limpio y sigues las mejores prácticas.',
        )->prompt(
            "Genera {$tipo} en Laravel 13. Solo dame el código, sin explicaciones.",
            model: 'gemma-3-12b-it-IQ4_XS'
        );

        return response()->json([
            'codigo' => $response->text,
            'solicitud' => $tipo,
        ]);
    }

    /**
     * Análisis de texto con Gemma 3
     */
    public function analizar(Request $request): JsonResponse
    {
        $texto = $request->input('texto', 'Este producto es increíble, lo recomiendo totalmente');

        $response = agent(
            instructions: 'Eres un analizador de sentimientos. Respondes solo con positivo, negativo o neutro.',
        )->prompt(
            "Clasifica el siguiente texto: {$texto}",
            model: 'gemma-3-12b-it-IQ4_XS'
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
            model: 'gemma-3-12b-it-IQ4_XS'
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
            model: 'gemma-3-12b-it-IQ4_XS'
        );

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
     * El agente lee los posts y genera preguntas de quiz.
     */
    public function quizPosts(Request $request): JsonResponse
    {
        $posts = Post::where('posted', 'si')
            ->limit(5)
            ->get(['title', 'content']);

        if ($posts->isEmpty()) {
            return response()->json([
                'error' => 'No hay posts disponibles para generar el quiz',
            ], 404);
        }

        $contenido = $posts->map(fn ($post) => "Título: {$post->title}\nContenido: {$post->content}")->join("\n\n---\n\n");

        $resultado = (new QuizAgent)->prompt(
            "Basándote en los siguientes posts, genera 5 preguntas de verdadeiro o falso:\n\n{$contenido}",
            model: 'gemma-3-12b-it-IQ4_XS'
        );

        return response()->json([
            'quiz' => $resultado->toArray(),
            'posts_utilizados' => $posts->count(),
        ]);
    }
}
