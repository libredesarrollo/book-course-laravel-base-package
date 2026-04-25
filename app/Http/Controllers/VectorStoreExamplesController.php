<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Stores;

/**
 * Controlador de ejemplos para Vector Stores del Laravel AI SDK.
 *
 * Los Vector Stores permiten crear colecciones buscables de archivos que pueden ser
 * usados para Retrieval-Augmented Generation (RAG).
 */
class VectorStoreExamplesController extends Controller
{
    /**
     * Ejemplo básico de creación de un vector store simple.
     * Usa Stores::create() con solo el nombre para crear un nuevo vector store.
     * El store se crea en el provider de IA (OpenAI, Anthropic, etc.)
     */
    public function createBasicStore(): JsonResponse
    {
        $store = Stores::create('Knowledge Base');

        return response()->json([
            'message' => 'Vector store creado exitosamente',
            'store_id' => $store->id,
            'name' => $store->name,
            'ready' => $store->ready,
        ]);
    }

    /**
     * Ejemplo de creación de vector store con opciones adicionales.
     * Puedes agregar descripción y configurar expiración cuando está inactivo.
     */
    public function createStoreWithOptions(): JsonResponse
    {
        $store = Stores::create(
            name: 'Documentation Archive',
            description: 'Documentación técnica y referencias de Laravel 13.',
            expiresWhenIdleFor: new \DateInterval('P30D'),
        );

        return response()->json([
            'store_id' => $store->id,
            'name' => $store->name,
            'description' => $store->description ?? 'Sin descripción',
            'expires_at' => $store->expiresAt ?? 'No expira',
        ]);
    }

    /**
     * Ejemplo de obtener un vector store existente por su ID.
     * Usa Stores::get() para recuperar un store previamente creado.
     */
    public function getStore(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');

        if (! $storeId) {
            return response()->json(['error' => 'Se requiere store_id'], 422);
        }

        $store = Stores::get($storeId);

        return response()->json([
            'id' => $store->id,
            'name' => $store->name,
            'file_counts' => $store->fileCounts,
            'ready' => $store->ready,
        ]);
    }

    /**
     * Ejemplo de eliminar un vector store.
     * Usa el método delete() en la instancia del store.
     */
    public function deleteStore(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');

        if (! $storeId) {
            return response()->json(['error' => 'Se requiere store_id'], 422);
        }

        $store = Stores::get($storeId);
        $store->delete();

        return response()->json([
            'message' => 'Vector store eliminado exitosamente',
            'store_id' => $storeId,
        ]);
    }

    /**
     * Ejemplo de agregar un archivo a un vector store desde una ruta.
     * El archivo se almacena en el provider y se indexa automáticamente.
     */
    public function addFileFromPath(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');
        $filePath = $request->get('file_path', storage_path('app/docs/manual.pdf'));

        if (! $storeId) {
            return response()->json(['error' => 'Se requiere store_id'], 422);
        }

        $store = Stores::get($storeId);
        $document = $store->add(Document::fromPath($filePath));

        return response()->json([
            'message' => 'Archivo agregado al store',
            'document_id' => $document->id,
            'file_id' => $document->fileId,
        ]);
    }

    /**
     * Ejemplo de agregar un archivo desde storage (disk de Laravel).
     * Útil cuando el archivo ya está almacenado en el sistema de archivos de Laravel.
     */
    public function addFileFromStorage(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');
        $filename = $request->get('filename', 'manual.pdf');

        if (! $storeId) {
            return response()->json(['error' => 'Se requiere store_id'], 422);
        }

        $store = Stores::get($storeId);
        $document = $store->add(Document::fromStorage($filename));

        return response()->json([
            'message' => 'Archivo desde storage agregado',
            'document_id' => $document->id,
            'file_id' => $document->fileId,
        ]);
    }

    /**
     * Ejemplo de agregar un archivo con metadatos.
     * Los metadatos pueden usarse para filtrar búsquedas posteriormente.
     */
    public function addFileWithMetadata(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');
        $filePath = $request->get('file_path', storage_path('app/docs/guide.pdf'));

        if (! $storeId) {
            return response()->json(['error' => 'Se requiere store_id'], 422);
        }

        $store = Stores::get($storeId);
        $document = $store->add(
            Document::fromPath($filePath),
            metadata: [
                'author' => 'Andres Cruz',
                'department' => 'Engineering',
                'year' => 2026,
                'version' => '1.0',
            ]
        );

        return response()->json([
            'message' => 'Archivo con metadatos agregado',
            'document_id' => $document->id,
            'metadata' => [
                'author' => 'Andres Cruz',
                'department' => 'Engineering',
                'year' => 2026,
            ],
        ]);
    }

    /**
     * Ejemplo de eliminar un archivo de un vector store.
     * El archivo se elimina del store pero puede seguir existiendo en file storage.
     */
    public function removeFile(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');
        $fileId = $request->get('file_id');

        if (! $storeId || ! $fileId) {
            return response()->json(['error' => 'Se requieren store_id y file_id'], 422);
        }

        $store = Stores::get($storeId);
        $store->remove($fileId);

        return response()->json([
            'message' => 'Archivo eliminado del store',
            'file_id' => $fileId,
        ]);
    }

    /**
     * Ejemplo de eliminar archivo y también el file storage.
     * El flag deleteFile: true elimina el archivo completamente.
     */
    public function removeFileAndStorage(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');
        $fileId = $request->get('file_id');

        if (! $storeId || ! $fileId) {
            return response()->json(['error' => 'Se requieren store_id y file_id'], 422);
        }

        $store = Stores::get($storeId);
        $store->remove($fileId, deleteFile: true);

        return response()->json([
            'message' => 'Archivo eliminado del store y storage',
            'file_id' => $fileId,
        ]);
    }

    /**
     * Ejemplo de crear store, guardar archivo y agregar en un solo paso.
     * Flujo completo: crear store → guardar archivo → agregar al store.
     */
    public function fullWorkflow(): JsonResponse
    {
        // Paso 1: Crear el vector store
        $store = Stores::create('Product Documentation');

        // Paso 2: Guardar archivo en el provider
        $file = Document::fromPath(storage_path('app/docs/product-guide.pdf'))
            ->as('product-guide.pdf')
            ->put();

        // Paso 3: Agregar el archivo al store
        $document = $store->add($file->id);

        return response()->json([
            'message' => 'Flujo completo ejecutado',
            'store_id' => $store->id,
            'store_name' => $store->name,
            'file_id' => $file->id,
            'document_id' => $document->id,
        ]);
    }

    /**
     * Ejemplo de búsqueda híbrida: similarity search local + vector store.
     * Combina la búsqueda vectorial en PostgreSQL con archivos en el vector store del provider.
     */
    public function hybridSearch(): JsonResponse
    {
        $query = 'Laravel best practices';

        // Búsqueda local en PostgreSQL (vectores almacenados en BD)
        $localResults = \App\Models\Document::query()
            ->whereVectorSimilarTo('embedding', $query, minSimilarity: 0.5)
            ->limit(5)
            ->get(['id', 'title', 'content']);

        // Aquí podrías combinar con búsqueda en vector store del provider
        // Esto requeriría un agente con FileSearch tool

        return response()->json([
            'query' => $query,
            'local_results_count' => $localResults->count(),
            'local_results' => $localResults->map(fn ($doc) => [
                'id' => $doc->id,
                'title' => $doc->title,
                'preview' => substr($doc->content, 0, 100).'...',
            ]),
        ]);
    }

    /**
     * Ejemplo de listado de archivos en un store (simulado).
     * Muestra cómo acceder a la información del store.
     */
    public function listStoreInfo(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');

        if (! $storeId) {
            return response()->json(['error' => 'Se requiere store_id'], 422);
        }

        $store = Stores::get($storeId);

        return response()->json([
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'description' => $store->description ?? null,
                'file_counts' => $store->fileCounts,
                'ready' => $store->ready,
                'created_at' => $store->createdAt ?? null,
                'expires_at' => $store->expiresAt ?? null,
            ],
        ]);
    }

    /**
     * Ejemplo de crear múltiples stores para diferentes propósitos.
     * Patrón común: diferentes stores para diferentes tipos de contenido.
     */
    public function createMultipleStores(): JsonResponse
    {
        $stores = [
            Stores::create('Technical Documentation'),
            Stores::create('User Guides'),
            Stores::create('API References'),
        ];

        return response()->json([
            'message' => 'Múltiples stores creados',
            'stores' => collect($stores)->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ]),
        ]);
    }

    /**
     * Ejemplo de archivo desde string (generar documento en memoria).
     * Útil para crear documentos dinámicamente sin archivos físicos.
     */
    public function addFileFromString(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');

        if (! $storeId) {
            return response()->json(['error' => 'Se requiere store_id'], 422);
        }

        $store = Stores::get($storeId);

        $content = "Este es un documento generado dinámicamente.\n".
            'Contenido sobre Laravel 13 y sus nuevas características.';

        $document = $store->add(
            Document::fromString($content, 'text/plain')
                ->as('dynamic-document.txt')
        );

        return response()->json([
            'message' => 'Documento dinámico agregado',
            'document_id' => $document->id,
            'file_id' => $document->fileId,
        ]);
    }

    /**
     * Ejemplo de eliminar store por ID directamente (sin obtener instancia).
     * Forma más directa de eliminar un store usando la clase Stores.
     */
    public function deleteStoreById(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');

        if (! $storeId) {
            return response()->json(['error' => 'Se requiere store_id'], 422);
        }

        Stores::delete($storeId);

        return response()->json([
            'message' => 'Store eliminado via Stores::delete()',
            'store_id' => $storeId,
        ]);
    }
}
