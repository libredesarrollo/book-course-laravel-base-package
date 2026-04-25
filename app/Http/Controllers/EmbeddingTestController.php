<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Ai\Embeddings;

class EmbeddingTestController extends Controller
{

 

    /**
     * Genera embeddings usando PHP AI SDK (enfoque portable - funciona con cualquier provider)
     */
    public function generateEmbeddings(): JsonResponse
    {
        $documents = Document::all();

        $contents = $documents->pluck('content')->toArray();

        $response = Embeddings::for($contents)->generate();

        foreach ($documents as $index => $document) {
            $document->embedding = $response->embeddings[$index];
            $document->save();
        }

        return response()->json([
            'message' => 'Embeddings generated via AI SDK',
            'method' => 'php-ai-sdk',
            'count' => count($response->embeddings),
        ]);
    }

    /**
     * Búsqueda vectorial similarity (usa el query como string - Laravel genera embedding automáticamente)
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', 'best wineries');

        $results = Document::query()
            ->whereVectorSimilarTo('embedding', $query, minSimilarity: 0.4)
            ->limit(10)
            ->get(['title', 'content']);

        return response()->json([
            'query' => $query,
            'results' => $results,
            'count' => $results->count(),
        ]);
    }

    /**
     * Búsqueda con embedding pre-generado (más eficiente para múltiples búsquedas)
     */
    public function searchWithEmbedding(Request $request): JsonResponse
    {
        $query = $request->get('q', 'best wineries asasas asasasasas asxx');

        $queryEmbedding = Str::of($query)->toEmbeddings();

        $results = Document::query()
            ->select('*')
            ->selectVectorDistance('embedding', $queryEmbedding, as: 'distance')
            ->whereVectorDistanceLessThan('embedding', $queryEmbedding, maxDistance: 0.6)
            ->orderByVectorDistance('embedding', $queryEmbedding)
            ->limit(10)
            ->get(['title', 'content', 'distance']);

        return response()->json([
            'query' => $query,
            'query_embedding_dimensions' => count($queryEmbedding),
            'results' => $results,
        ]);
    }

    /**
     * Prueba el helper Str::toEmbeddings() (Stringable)
     */
    public function testStringable(): JsonResponse
    {
        $embedding = Str::of('Napa Valley has great wine.')->toEmbeddings();
        // $embedding = str('Napa Valley has great wine.')->toEmbeddings();

        return response()->json([
            'text' => 'Napa Valley has great wine.',
            'embedding' => $embedding,
            'dimensions' => count($embedding),
            'method' => 'Stringable->toEmbeddings()',
        ]);
    }

    /**
     * Ejemplo de caching de embeddings
     */
    public function testCachedEmbeddings(): JsonResponse
    {
        $text = 'Laravel is awesome.';
        $text2 = 'Laravel is awesome.';

        // Primera llamada - genera embedding
        $start1 = microtime(true);
        $embedding1 = Str::of($text)->toEmbeddings(cache: true);
        $time1 = microtime(true) - $start1;

        // Segunda llamada - retorna del cache
        $start2 = microtime(true);
        $embedding2 = Str::of($text2)->toEmbeddings(cache: 3600);
        $time2 = microtime(true) - $start2;

        return response()->json([
            'text' => $text,
            'first_call_seconds' => round($time1, 4),
            'cached_call_seconds' => round($time2, 4),
            'embeddings_match' => $embedding1 === $embedding2,
        ]);
    }

    /**
     * Muestra todos los documentos con sus embeddings
     */
    public function listDocuments(): JsonResponse
    {
        $documents = Document::all(['id', 'title', 'content', 'embedding']);

        return response()->json([
            'documents' => $documents,
            'count' => $documents->count(),
        ]);
    }
}
