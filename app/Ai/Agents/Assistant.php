<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\WebFetch;
use Stringable;

// #[Provider(Lab::Ollama)]
// #[Model('gemma3:1b')]
class Assistant implements Agent, HasTools
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant. For URL requests, summarize the content provided by the user.';
    }

    public function tools(): iterable
    {
        return [
            new WebFetch,
        ];
    }
}
