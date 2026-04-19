<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;


/**
 * Agente para generar quiz de Verdadero/Falso basado en posts.
 *
 * Schema devuelto:
 * - preguntas: array de objetos
 *   - pregunta: string
 *   - respuesta: boolean (true = verdadero, false = falso)
 *   - explicacion: string
 */
#[Timeout(30)]
class QuizAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'Eres un generador de preguntas de quiz. Basándote en el contenido de los posts, generas preguntas de verdadero o falso. Las preguntas deben ser claras y la respuesta correcta debe estar basada en el contenido.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'preguntas' => $schema->array()->items(
                $schema->object([
                    'pregunta' => $schema->string()->required(),
                    'respuesta' => $schema->boolean()->required(),
                    'explicacion' => $schema->string()->required(),
                ])
            )->required(),
        ];
    }
}
