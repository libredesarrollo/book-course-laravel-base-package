<?php

use App\Mcp\Prompts\SummarizePostsPrompt;
use App\Mcp\Resources\AppInfoResource;
use App\Mcp\Servers\PostServer;
use App\Mcp\Tools\GetPostTool;
use App\Mcp\Tools\ListPostsTool;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    /*
     * Las migraciones de pgvector/documentos no corren en SQLite, por lo que
     * solo migramos las tablas necesarias para este test.
     */
    Artisan::call('migrate:fresh', [
        '--force' => true,
        '--realpath' => true,
        '--path' => [
            database_path('migrations/0001_01_01_000000_create_users_table.php'),
            database_path('migrations/2026_09_26_093945_create_categories_table.php'),
            database_path('migrations/2026_09_26_094316_create_posts_table.php'),
            database_path('migrations/2026_09_26_094319_add_foreign_keys_to_posts_table.php'),
        ],
    ]);
});

function createPost(array $attributes = []): Post
{
    $user = User::first() ?? User::factory()->create();
    $categoryId = DB::table('categories')->value('id')
        ?? DB::table('categories')->insertGetId(['title' => 'General', 'slug' => 'general']);

    return Post::create(array_merge([
        'title' => 'Primer post',
        'slug' => 'primer-post',
        'description' => 'Una descripción',
        'content' => 'Contenido completo del post',
        'posted' => true,
        'category_id' => $categoryId,
        'user_id' => $user->id,
    ], $attributes));
}

it('lists posts through the list posts tool', function () {
    createPost(['title' => 'Laravel MCP']);
    createPost(['title' => 'Otro post', 'slug' => 'otro-post']);

    $response = PostServer::tool(ListPostsTool::class, ['per_page' => 10]);

    $response
        ->assertOk()
        ->assertSee('Laravel MCP')
        ->assertSee('Otro post');
});

it('filters posts by search term', function () {
    createPost(['title' => 'Introducción a MCP']);
    createPost(['title' => 'Recipes', 'slug' => 'recipes']);

    $response = PostServer::tool(ListPostsTool::class, ['search' => 'MCP']);

    $response
        ->assertOk()
        ->assertSee('Introducción a MCP')
        ->assertDontSee('Recipes');
});

it('rejects an invalid per_page value', function () {
    PostServer::tool(ListPostsTool::class, ['per_page' => 500])
        ->assertHasErrors();
});

it('gets a single post by id', function () {
    $post = createPost(['title' => 'Post recuperable']);

    $response = PostServer::tool(GetPostTool::class, ['id' => $post->id]);

    $response
        ->assertOk()
        ->assertSee('Post recuperable')
        ->assertSee('Contenido completo del post');
});

it('returns an error when the post does not exist', function () {
    PostServer::tool(GetPostTool::class, ['id' => 9999])
        ->assertHasErrors(['El post con ID 9999 no existe.']);
});

it('exposes app info as a resource', function () {
    createPost();
    User::factory()->create();

    PostServer::resource(AppInfoResource::class)
        ->assertOk()
        ->assertSee(config('app.name'))
        ->assertSee('"posts":1');
});

it('builds the summarize posts prompt', function () {
    createPost(['title' => 'MCP en Laravel', 'posted' => 'yes']);

    $response = PostServer::prompt(SummarizePostsPrompt::class, ['tone' => 'formal']);

    $response
        ->assertOk()
        ->assertSee('tono formal')
        ->assertSee('MCP en Laravel');
});

it('ignores unpublished posts in the summarize prompt', function () {
    createPost(['title' => 'Publicado', 'slug' => 'publicado', 'posted' => 'yes']);
    createPost(['title' => 'Borrador', 'slug' => 'borrador', 'posted' => 'not']);

    PostServer::prompt(SummarizePostsPrompt::class, [])
        ->assertOk()
        ->assertSee('Publicado')
        ->assertDontSee('Borrador');
});

it('registers the web and local server routes', function () {
    $this->artisan('route:list', ['--path' => 'mcp'])->assertExitCode(0);
});
