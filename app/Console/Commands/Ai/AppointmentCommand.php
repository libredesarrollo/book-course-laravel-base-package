<?php

namespace App\Console\Commands\Ai;

use App\Ai\Agents\Appointment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:appointment {action?}')]
#[Description('Schedule appointments or list them')]
class AppointmentCommand extends Command
{
    public function handle(): int
    {
        $action = $this->argument('action') ?? $this->choice(
            'Action',
            ['schedule', 'list'],
            'schedule'
        );

        if ($action === 'list') {
            $this->listAppointments();

            return Command::SUCCESS;
        }

        $date = $this->ask('Appointment date (e.g., 2026-05-15 14:00)');
        $message = $this->ask('Message');

        $this->info('Scheduling appointment...');

        $response = (new Appointment)->prompt("Schedule an appointment. Date: {$date}, Message: {$message}", model: 'gemma-3-12b-it-IQ4_XS');

        $this->info('Response:');
        $this->line($response->text);

        return Command::SUCCESS;
    }

    private function listAppointments(): void
    {
        $files = glob(storage_path('app/appointments/*.json'));

        if (empty($files)) {
            $this->warn('No appointments found.');

            return;
        }

        $this->table(['Date', 'Message', 'Created'], collect($files)->map(function ($file) {
            $data = json_decode(file_get_contents($file), true);

            return [$data['date'] ?? '-', $data['message'] ?? '-', $data['created_at'] ?? '-'];
        })->all());
    }
}
