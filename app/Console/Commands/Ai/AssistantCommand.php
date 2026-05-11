<?php

namespace App\Console\Commands\Ai;

use App\Ai\Agents\Assistant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:assistant {prompt?}')]
#[Description('Interact with the Assistant AI agent using WebFetch tool')]
class AssistantCommand extends Command
{
    public function handle(): int
    {
        $prompt = $this->argument('prompt') ?? $this->ask('Enter your prompt');

        $this->info('Processing with Assistant...');

        $response = (new Assistant)->prompt($prompt);

        $this->info('Response:');
        $this->line($response->text);

        return Command::SUCCESS;
    }
}
