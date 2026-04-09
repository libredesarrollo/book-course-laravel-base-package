<?php

namespace App\Http\Controllers;

use App\Ai\Agents\PokemonAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Enums\Lab;

use function Laravel\Ai\agent;

/**
 * Controlador para ejemplos de uso de Gemma 3 con IA local (Ollama).
 *
 * Gemma 3 es un modelo de Google que puede ejecutarse localmente via Ollama.
 * Requiere tener Ollama instalado y el modelo descargado: ollama pull gemma3:12b
 *
 * Configuración:
 * - Jan.ai o Ollama debe estar ejecutándose en http://localhost:11434
 * - El modelo gemma3 debe estar descargado: ollama pull gemma3:12b
 */
class AgentsTestController extends Controller
{
    /**
     * Ejemplo 1: Chat básico con Gemma 3
     *
     * Uso del helper global agent() para crear un agente anónimo con IA local.
     * El modelo gemma3:12b-it-IQ4_XS es una versión cuantizada más ligera.
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
     * Ejemplo 2: Generación de código con Gemma 3
     *
     * Usar Gemma para generación de código es muy útil.
     * Este ejemplo genera código Laravel específico.
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
     * Ejemplo 3: Análisis de texto con Gemma 3
     *
     * Gemma puede analizar y clasificar texto.
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
     * Ejemplo 4: Fallback entre proveedores
     *
     * Si Ollama no está disponible, usa otro proveedor como backup.
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
     * Ejemplo 5: Lista de Pokemon con schema definido
     *
     * Usa el PokemonAgent con HasStructuredOutput para devolver
     * un schema JSON exactamente controlado.
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
}
