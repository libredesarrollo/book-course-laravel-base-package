<?php

namespace App\Http\Controllers\Ai;

use App\Ai\Agents\Assistant;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $prompt = $request->input('prompt', 'Hello, who are you?');

        $response = (new Assistant)->prompt($prompt);

        return response()->json([
            'respuesta' => $response->text,
            'modelo' => 'Assistant with WebFetch',
        ]);
    }

    public function fetchUrl(Request $request): JsonResponse
    {
        $url = $request->input('url');

        if (! $url) {
            return response()->json(['error' => 'URL is required'], 400);
        }

        $response = (new Assistant)->prompt("Fetch and summarize the content from: {$url}");

        return response()->json([
            'url' => $url,
            'contenido' => $response->text,
        ]);
    }
}
