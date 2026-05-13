<?php

namespace App\Ai\Agents;

use App\Ai\Tools\ScheduleAppointment;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

// #[Provider(Lab::Ollama)]
// #[Model('gemma3:1b')]
class Appointment implements Agent, HasTools
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are an appointment scheduling assistant. When the user provides a date and message, use the ScheduleAppointment tool to save it. Call the tool immediately with the provided date and message.';
    }

    public function tools(): iterable
    {
        return [
            new ScheduleAppointment,
        ];
    }
}
