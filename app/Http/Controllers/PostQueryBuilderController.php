<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Spatie\QueryBuilder\QueryBuilder;

class PostQueryBuilderController extends Controller
{
    /**
     * Ejemplo básico: filtrar posts por título.
     * URL: /posts?filter[title]=laravel
     *
     * Permite filtrar por los campos especificados en allowedFilters.
     * El filtro es parcial (contains) por defecto.
     */
    public function index(): mixed
    {
        $posts = QueryBuilder::for(Post::class)
            ->allowedFilters(
                'title',        // Filtro parcial por título
                'slug',         // Filtro parcial por slug
                'posted',       // Filtro exacto por estado (published/draft)
                'category_id',  // Filtro exacto por categoría
            )
            ->get();

        return response()->json($posts);
    }

    /**
     * Ejemplo: filtrado exacto y ordenamiento.
     * URL: /posts-filtered?filter[posted]=yes&sort=-created_at
     *
     * - filter[posted]=published: filtro exacto
     * - sort=-created_at: orden descendente por fecha de creación
     */
    public function filtered(): mixed
    {
        $posts = QueryBuilder::for(Post::class)
            ->allowedFilters('title', 'posted', 'category_id')
            ->allowedSorts('title', 'created_at', 'id')
            ->get();

        return response()->json($posts);
    }

    /**
     * Ejemplo: inclusión de relaciones (eager loading).
     * URL: /posts-include?include=user,category
     *
     * Carga las relaciones especificadas en la query string.
     * Evita el problema N+1 al incluir relaciones.
     */
    public function withIncludes(): mixed
    {
        $posts = QueryBuilder::for(Post::class)
            ->allowedIncludes('user', 'category')
            ->get();

        return response()->json($posts);
    }

    /**
     * Ejemplo: selección de campos específicos.
     * URL: /posts-fields?fields=id,title,slug
     *
     * Permite seleccionar solo los campos necesarios,
     * útil para reducir el tamaño de la respuesta JSON.
     */
    public function withFields(): mixed
    {
        $posts = QueryBuilder::for(Post::class)
            ->allowedFields('id', 'title', 'slug', 'description', 'posted', 'created_at')
            ->get();

        return response()->json($posts);
    }

    /**
     * Ejemplo completo: combina filtros, ordenamiento, inclusiones y paginación.
     * URL: /posts-full?filter[title]=tutorial&filter[posted]=published&sort=-created_at&include=user&fields=id,title,slug&page=2&per_page=15
     */
    public function fullExample(): mixed
    {
        $posts = QueryBuilder::for(Post::class)
            // Filtros permitidos
            ->allowedFilters('title', 'slug', 'posted', 'category_id', 'user_id')
            // Ordenamientos permitidos (el prefijo "-" indica orden descendente)
            ->allowedSorts('title', 'created_at', 'id')
            // Relaciones que pueden ser incluidas
            ->allowedIncludes('user', 'category')
            // Campos que pueden ser seleccionados
            ->allowedFields('id', 'title', 'slug', 'description', 'posted', 'category_id', 'user_id', 'created_at', 'updated_at')
            // Paginación con parámetros personalizables
            ->paginate()
            ->appends(request()->query());

        return response()->json($posts);
    }

    /**
     * Ejemplo: usar QueryBuilder sobre una consulta existente.
     * Útil cuando necesitas partir de una query con condiciones previas.
     * URL: /posts-active?filter[title]=news&include=user
     */
    public function fromExistingQuery(): mixed
    {
        // Comenzar desde una consulta existente con condiciones previas
        $query = Post::where('posted', 'published');

        $posts = QueryBuilder::for($query)
            ->allowedFilters('title', 'description')
            ->allowedIncludes('user', 'category')
            ->allowedSorts('title', 'created_at')
            ->paginate()
            ->appends(request()->query());

        return response()->json($posts);
    }

    /**
     * Ejemplo: filtros con modificadores especiales.
     * URL: /posts-modifiers?filter[title]=laravel&filter[exact_description]=Tutorial
     *
     * - filter[title]: filtro parcial (contiene)
     * - filter[exact_description]: filtro exacto
     */
    public function withFilterModifiers(): mixed
    {
        $posts = QueryBuilder::for(Post::class)
            ->allowedFilters(
                'title',                    // Filtro parcial (por defecto)
                'slug',                     // Filtro parcial (por defecto)
                'posted',                   // Filtro exacto
                'description:exact',        // filter[description] será exacto
            )
            ->get();

        return response()->json($posts);
    }

    /**
     * Ejemplo: múltiples valores en un filtro.
     * URL: /posts-multiple?filter[posted]=published,draft
     *
     * Por defecto usa coma como separador. Cambiar con setMultiValueDelimiter().
     */
    public function withMultipleValues(): mixed
    {
        $posts = QueryBuilder::for(Post::class)
            ->allowedFilters('title', 'posted')
            ->get();

        return response()->json($posts);
    }

    /**
     * Ejemplo: endpoint público con todas las opciones habilitadas.
     * Este es el patrón más común para APIs públicas.
     * URL: /api/posts?filter[title]=tutorial&sort=-created_at&include=user&fields=id,title,slug&page=1&per_page=10
     */
    public function apiIndex(): mixed
    {
        return QueryBuilder::for(Post::class)
            ->allowedFilters('title', 'slug', 'description', 'posted', 'category_id', 'user_id')
            ->allowedSorts('id', 'title', 'created_at', 'updated_at')
            ->allowedIncludes('user', 'category')
            ->allowedFields('id', 'title', 'slug', 'content', 'description', 'posted', 'image', 'category_id', 'user_id', 'created_at', 'updated_at')
            ->paginate()
            ->appends(request()->query());
    }
}