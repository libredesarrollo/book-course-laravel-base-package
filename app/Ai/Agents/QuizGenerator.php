<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Agente para generar quiz de Verdadero/Falso basado en Posts.
 *
 * Forma "pro": inyectar el contenido directamente al agente
 * via el método withContext() en lugar de concatenar en el prompt.
 *
 * Schema devuelto:
 * - preguntas: array de objetos
 *   - pregunta: string
 *   - respuesta: boolean (true = verdadero, false = falso)
 *   - explicacion: string
 */
class QuizGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    protected string $contexto = '';

    /**
     * Inyecta el contenido de los posts al agente.
     * El agente se encarga de procesar la información.
     */
    public function withContext(Collection $posts): self
    {
        $this->contexto = $posts->map(fn ($post) => "Título: {$post->title}\nContenido: {$post->content}")
            ->join("\n\n---\n\n");

        return $this;
    }

    public function instructions(): Stringable|string
    {
        return "Eres un experto docente. Tu tarea es leer el siguiente material educativo y generar un quiz de verdadero o falso.\n\nMATERIAL DE REFERENCIA:\n{$this->contexto}";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'preguntas' => $schema->array()->items(
                $schema->object([
                    'pregunta' => $schema->string()->description('La afirmación de verdadero/falso'),
                    'respuesta' => $schema->boolean()->description('True si es verdadero, False si es falso'),
                    'explicacion' => $schema->string()->description('Por qué es verdadera o falsa basándose en el texto'),
                ])
            )->required(),
        ];
    }
}
