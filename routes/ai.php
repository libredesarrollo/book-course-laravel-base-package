<?php

use App\Mcp\Servers\PostServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| Rutas MCP
|--------------------------------------------------------------------------
|
| Servidor accessible por HTTP (clientes remotos como Claude Desktop,
| Cursor, etc. con transporte streamable HTTP).
|
*/
Mcp::web('/mcp/posts', PostServer::class);

/*
|--------------------------------------------------------------------------
| Servidor local
|--------------------------------------------------------------------------
|
| Se ejecuta como comando Artisan: php artisan mcp:start posts
|
*/
Mcp::local('posts', PostServer::class);
