<?php

use App\Exports\PostsExport;
use App\Http\Controllers\AgentsTestController;
use App\Http\Controllers\EmbeddingTestController;
use App\Http\Controllers\PaymentPaypalController;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use function Laravel\Ai\agent;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/is-mobile', function () {
    dd(isMobile());

    return view('welcome');
});
Route::get('/export-excel', function () {
    return Excel::download(new PostsExport, 'posts.xlsx');
});
Route::get('/laravel-ia-text', function () {

    // $response = agent(
    //     instructions: 'Eres un asistente experto en Laravel.',)->prompt(
    //     'Genera una lista de 3 temas de Laravel 13 en formato JSON',
    //     provider: Lab::Gemini,
    //     model: 'gemini-2.0-flash',);
    $response = agent(
        instructions: 'Eres un asistente experto en Laravel.',
    )->prompt(
        'Genera una lista de 3 temas de Laravel 13 en formato JSON',
        // provider: 'openai', // ESTO ES VITAL
        model: 'gemma-3-12b-it-IQ4_XS'
    );

    // $response = AI::chat('Explícame qué es un Repository Pattern en Laravel')
    // ->model('llama3') // El nombre del modelo que tengas en local
    // ->send();

    dd($response);
});

Route::get('/laravel-ia-text2', function () {
    try {
        $response = agent(
            instructions: 'Eres un asistente experto en Laravel.',
        )->prompt(
            'Genera una lista de 3 temas de Laravel 13 en formato JSON',
            provider: 'openai',
            model: 'gemma-3-12b-it-IQ4_XS' // Copiado exactamente de tu JSON
        );

        dd($response);
    } catch (Exception $e) {
        // Esto nos dirá qué está pasando realmente si falla
        return response()->json([
            'message' => $e->getMessage(),
            'check_jan' => 'Asegúrate de que el servidor en Jan.ia esté en "Started"',
        ], 500);
    }
});

Route::get('/qr', function () {
    // QrCode::format('png')->generate('DesarrolloLibre');
    QrCode::format('png')->size(700)->color(255, 0, 0)
        // ->merge('/assets/img/logo.png', .3, true)
        ->generate('Desarrollo libre Andres', '../public/qrcode.png');

    return view('welcome');
});

Route::get('/paypal', [PaymentPaypalController::class, 'paypal']);
Route::post('/paypal-process-order/{order}', [PaymentPaypalController::class, 'paypalProcessOrder']);

/*
|--------------------------------------------------------------------------
| Rutas de Gemma 3 con IA Local (Ollama)
|--------------------------------------------------------------------------
|
| Ejemplos de uso del modelo Gemma 3 corriendo localmente via Ollama.
|
| Requisitos:
| - Tener Ollama instalado (https://ollama.com)
| - Descargar modelo: ollama pull gemma3:12b
| - Ejecutar Ollama: ollama serve
| - O usar Jan.ai para interfaz gráfica
|
*/

/*** AGENTES Anonimos */

// Chat básico
Route::get('/agents/chat', [AgentsTestController::class, 'chat']);

// Generación de código
Route::get('/agents/generar-codigo', [AgentsTestController::class, 'generarCodigo']);

// Análisis de sentimientos
Route::get('/agents/analizar', [AgentsTestController::class, 'analizar']);

// Streaming de respuestas (demo)
Route::get('/agents/streaming', [AgentsTestController::class, 'streaming']);

// Fallback entre proveedores
Route::get('/agents/fallback', [AgentsTestController::class, 'conFallback']);

/*** AGENTES */
// Structured Output: Lista de Pokemon con PokemonAgent
Route::get('/agents/pokemon-lista', [AgentsTestController::class, 'listaPokemones']);

Route::get('/agents/sales-coach', [AgentsTestController::class, 'salesCoach']);

// Quiz Verdadero/Falso basado en Posts (versión pro con withContext)
Route::get('/agents/quiz-posts', [AgentsTestController::class, 'quizPosts']);

// Quiz Verdadero/Falso basado en Posts (versión simple con prompt)
Route::get('/agents/quiz-simple', [AgentsTestController::class, 'quizPostsSimple']);

/*
|--------------------------------------------------------------------------
| Embeddings Test Routes
|--------------------------------------------------------------------------
*/
// listado de documentos
Route::get('/embeddings/list', [EmbeddingTestController::class, 'listDocuments']);
// generar columna vector
Route::get('/embeddings/generate', [EmbeddingTestController::class, 'generateEmbeddings']);
// busqueda por similitud o cercania
Route::get('/embeddings/search', [EmbeddingTestController::class, 'search']);
Route::get('/embeddings/search-with-embedding', [EmbeddingTestController::class, 'searchWithEmbedding']);
// Prueba el helper Str::toEmbeddings() (Stringable)
Route::get('/embeddings/test-stringable', [EmbeddingTestController::class, 'testStringable']);
// cache del embedding
Route::get('/embeddings/test-cached', [EmbeddingTestController::class, 'testCachedEmbeddings']);
