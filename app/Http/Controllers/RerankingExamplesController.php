<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Reranking;

class RerankingExamplesController extends Controller
{
    /**
     * Ejemplo básico de reranking con un array de strings simples.
     * Usa Reranking::of() para pasar los documentos y luego rerank() con la query.
     * Retorna cada documento con su score de relevancia y el índice original.
     */
    public function basicReranking(): JsonResponse
    {
        $documents = [
            'Django is a Python web framework.',
            'Laravel is a PHP web application framework.',
            'React is a JavaScript library for building user interfaces.',
            'Ruby on Rails is a Ruby web framework.',
            'Vue.js is a progressive JavaScript framework.',
        ];

        $response = Reranking::of($documents)->rerank('PHP frameworks');

        $results = collect($response)->map(fn ($doc) => [
            'document' => $doc->document,
            'score' => $doc->score,
            'original_index' => $doc->index,
        ]);

        return response()->json([
            'query' => 'PHP frameworks',
            'reranked' => $results,
        ]);
    }

    /**
     * Ejemplo de reranking con límite de resultados.
     * Útil cuando tienes muchos documentos y solo necesitas los top N más relevantes.
     * El método limit() restringe la cantidad de documentos retornados por el reranker.
     */
    public function withLimit(): JsonResponse
    {
        $documents = collect(range(1, 20))->map(fn ($i) => "Document number {$i} about PHP Laravel framework");

        $response = Reranking::of($documents->toArray())
            ->limit(5)
            ->rerank('Laravel tutorial');

        $results = collect($response)->map(fn ($doc) => [
            'document' => $doc->document,
            'score' => $doc->score,
        ]);

        return response()->json([
            'top_5_results' => $results,
        ]);
    }

    /**
     * Ejemplo de reranking especificando un provider específico (Cohere).
     * Útil cuando quieres usar un provider específico en lugar del default.
     * Cohere y Jina son los providers que soportan reranking.
     */
    public function withSpecificProvider(): JsonResponse
    {
        $documents = [
            'Python machine learning tutorial',
            'Laravel PHP best practices',
            'JavaScript ES6 features',
            'Go concurrency patterns',
            'Rust memory safety',
        ];

        $response = Reranking::of($documents)
            ->rerank('web development frameworks', provider: Lab::Cohere);

        $results = collect($response)->map(fn ($doc) => [
            'document' => $doc->document,
            'score' => $doc->score,
        ]);

        return response()->json([
            'provider' => 'Cohere',
            'results' => $results,
        ]);
    }

    /**
     * Ejemplo de reranking directamente sobre modelos Eloquent (Collection macro).
     * Usa el método rerank() disponible en las Collections de Laravel.
     * Ideal para rerankear resultados de una consulta a la base de datos.
     */
    public function rerankEloquentModels(): JsonResponse
    {
        $posts = Document::select('title', 'content')->get();

        $reranked = $posts->rerank('content', 'wine vineyards Napa');

        return response()->json([
            'query' => 'wine vineyards Napa',
            'results' => collect($reranked)->take(5)->map(fn ($doc) => $doc['document']),
        ]);
    }

    /**
     * Ejemplo de reranking usando arrays asociativos con múltiples campos.
     * Los documentos pueden contener objetos/arrays con múltiples campos.
     * El reranker usa todos los campos del objeto para calcular la relevancia.
     */
    public function rerankMultipleFields(): JsonResponse
    {
        $documents = [
            ['title' => 'Laravel 11 Release', 'body' => 'New features in Laravel 11 including improved queue handling and reduced configuration.'],
            ['title' => 'React 19 Beta', 'body' => 'React 19 brings server components and improved hooks for better performance.'],
            ['title' => 'Laravel Forge', 'body' => 'Deploy and manage PHP applications on cloud servers with Forge.'],
            ['title' => 'Vue 3 Composition', 'body' => 'Vue 3 uses composition API for better code organization and reusability.'],
        ];

        $response = Reranking::of($documents)->rerank('Laravel deployment server');

        $results = collect($response)->map(fn ($doc) => [
            'title' => $documents[$doc->index]['title'],
            'score' => $doc->score,
        ]);

        return response()->json([
            'query' => 'Laravel deployment server',
            'results' => $results,
        ]);
    }

    /**
     * Ejemplo de reranking usando un closure para construir el documento.
     * Útil cuando quieres combinar múltiples campos o transformar el contenido
     * antes de enviarlo al reranker. El closure recibe cada item y retorna un string.
     */
    public function rerankWithClosure(): JsonResponse
    {
        $products = collect([
            ['name' => 'iPhone 15 Pro', 'description' => 'Latest Apple phone with titanium design and A17 chip.'],
            ['name' => 'Samsung Galaxy S24', 'description' => 'Android flagship with AI features and excellent camera.'],
            ['name' => 'MacBook Pro M3', 'description' => 'Professional laptop with M3 chip and Liquid Retina display.'],
            ['name' => 'AirPods Pro', 'description' => 'Wireless earbuds with active noise cancellation.'],
        ]);

        $reranked = $products->rerank(
            fn ($product) => $product['name'].' '.$product['description'],
            'premium audio device'
        );

        return response()->json([
            'query' => 'premium audio device',
            'results' => collect($reranked)->map(fn ($doc) => [
                'name' => $doc['document']['name'],
                'score' => $doc['score'],
            ]),
        ]);
    }

    /**
     * Ejemplo de búsqueda híbrida: combinación de búsqueda vectorial + reranking.
     * Paso 1: Usa similarity search para obtener candidatos (más cercanos vectorialmente).
     * Paso 2: Rerankeas esos candidatos con la query original para mejorar precisión.
     * Ideal cuando tienes muchos documentos y quieres los mejores resultados.
     */
    public function hybridSearchReranking(): JsonResponse
    {
        $query = 'Laravel tutorial';
        $queryEmbedding = str($query)->toEmbeddings();

        $candidates = Document::query()
            ->select('id', 'title', 'content')
            ->selectVectorDistance('embedding', $queryEmbedding, as: 'distance')
            ->orderByVectorDistance('embedding', $queryEmbedding)
            ->limit(20)
            ->get();

        if ($candidates->isEmpty()) {
            return response()->json(['message' => 'No candidates found']);
        }

        $documents = $candidates->map(fn ($doc) => $doc->content)->toArray();

        $reranked = Reranking::of($documents)
            ->limit(10)
            ->rerank($query);

        $results = collect($reranked)->map(fn ($doc) => [
            'content' => $doc->document,
            'vector_score' => $candidates[$doc->index]['distance'] ?? null,
            'rerank_score' => $doc->score,
        ]);

        return response()->json([
            'query' => $query,
            'hybrid_results' => $results,
        ]);
    }

    /**
     * Ejemplo de reranking manteniendo metadatos del documento.
     * Útil cuando necesitas preservar información adicional como ID, categoría, etc.
     * Puedes acceder a los metadatos a través del documento rerankeado.
     */
    public function rerankWithMetadata(): JsonResponse
    {
        $documents = [
            ['id' => 1, 'text' => 'Laravel API authentication with Sanctum', 'category' => 'auth', 'views' => 1000],
            ['id' => 2, 'text' => 'React hooks tutorial for beginners', 'category' => 'frontend', 'views' => 500],
            ['id' => 3, 'text' => 'Building REST APIs with Laravel', 'category' => 'api', 'views' => 2000],
            ['id' => 4, 'text' => 'Vue 3 state management with Pinia', 'category' => 'frontend', 'views' => 800],
            ['id' => 5, 'text' => 'Laravel queue workers configuration', 'category' => 'backend', 'views' => 600],
        ];

        $response = Reranking::of($documents)->rerank('Laravel API development');

        $results = collect($response)->map(fn ($doc) => [
            'id' => $documents[$doc->index]['id'],
            'text' => $doc->document['text'],
            'category' => $doc->document['category'],
            'rerank_score' => $doc->score,
        ]);

        return response()->json([
            'query' => 'Laravel API development',
            'results' => $results,
        ]);
    }

    /**
     * Ejemplo de filtrado por score de relevancia.
     * Después de rerankear, filtra los resultados que no cumplan un threshold mínimo.
     * Útil para descartar resultados poco relevantes y mostrar solo los de alta calidad.
     */
    public function filterByScore(): JsonResponse
    {
        $documents = [
            'Python web scraping tutorial',
            'Laravel Blade components guide',
            'JavaScript async await explained',
            'PHP Composer dependency management',
            'CSS Grid layout tutorial',
            'Laravel middleware configuration',
            'React server components',
        ];

        $response = Reranking::of($documents)->rerank('Laravel PHP framework');

        $threshold = 0.5;
        $filtered = collect($response)->filter(fn ($doc) => $doc->score >= $threshold);

        return response()->json([
            'query' => 'Laravel PHP framework',
            'threshold' => $threshold,
            'relevant_results' => $filtered->count(),
            'results' => $filtered->map(fn ($doc) => [
                'document' => $doc->document,
                'score' => $doc->score,
            ]),
        ]);
    }

    /**
     * Ejemplo de reranking por lotes (batch reranking).
     * Procesa múltiples queries en una sola llamada al controlador.
     * Útil para pre-rerankear documentos para diferentes queries frecuentes.
     */
    public function batchReranking(): JsonResponse
    {
        $queries = [
            'Laravel authentication',
            'React hooks',
            'Python data science',
        ];

        $allDocuments = [
            'Laravel Breeze authentication setup',
            'React useState hook tutorial',
            'Python pandas DataFrame operations',
            'Laravel Passport OAuth2 implementation',
            'React useEffect cleanup',
            'Python NumPy array manipulation',
            'Laravel Sanctum SPA authentication',
            'React Context API usage',
            'Python matplotlib visualization',
            'Laravel Pint code formatting',
        ];

        $results = [];
        foreach ($queries as $query) {
            $response = Reranking::of($allDocuments)->rerank($query);
            $results[$query] = collect($response)->first()->document;
        }

        return response()->json([
            'batch_results' => $results,
        ]);
    }

    /**
     * Ejemplo de paginación con reranking.
     * Útil cuando tienes muchos documentos y quieres mostrarlos en páginas.
     * Combina reranking con offset/limit para obtener la página solicitada.
     * Params: ?page=1&q=Laravel+tutorial
     */
    public function paginationWithReranking(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $perPage = 5;
        $query = $request->get('q', 'Laravel tutorial');

        $allDocuments = Document::pluck('content')->toArray();

        $response = Reranking::of($allDocuments)
            ->limit($perPage * ($page + 1))
            ->rerank($query);

        $allResults = collect($response);
        $total = $allResults->count();
        $pages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $pageResults = $allResults->slice($offset, $perPage)->map(fn ($doc) => [
            'document' => $doc->document,
            'score' => $doc->score,
        ]);

        return response()->json([
            'query' => $query,
            'current_page' => $page,
            'total_pages' => $pages,
            'results' => $pageResults,
        ]);
    }
}
