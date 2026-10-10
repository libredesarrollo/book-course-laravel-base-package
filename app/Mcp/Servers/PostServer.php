<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\SummarizePostsPrompt;
use App\Mcp\Resources\AppInfoResource;
use App\Mcp\Tools\GetPostTool;
use App\Mcp\Tools\ListPostsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Tool;

#[Name('Post Server')]
#[Version('1.0.0')]
#[Instructions('Este servidor expone los posts del blog: puede listarlos, buscar uno por su ID y leer información general de la aplicación.')]
class PostServer extends Server
{
    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        ListPostsTool::class,
        GetPostTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<Server\Resource>>
     */
    protected array $resources = [
        AppInfoResource::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<Prompt>>
     */
    protected array $prompts = [
        SummarizePostsPrompt::class,
    ];
}
