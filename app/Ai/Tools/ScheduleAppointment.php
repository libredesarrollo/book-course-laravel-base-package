<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ScheduleAppointment implements Tool
{
    public function description(): Stringable|string
    {
        return 'Schedule an appointment by storing the date and message in a JSON file within the storage/app directory.';
    }

    public function handle(Request $request): Stringable|string
    {
        $data = [
            'date' => $request['date'],
            'message' => $request['message'],
            'created_at' => now()->toIso8601String(),
        ];

        $filename = storage_path('app/appointments/'.now()->format('Y-m-d_His').'.json');

        if (! is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));

        $relativePath = 'appointments/'.now()->format('Y-m-d_His').'.json';

        return "Appointment scheduled successfully. Date: {$data['date']}, Message: {$data['message']}. Stored in: {$relativePath}";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()->required()->description('The appointment date and time in any format.'),
            'message' => $schema->string()->required()->description('A brief description or note about the appointment.'),
        ];
    }
}
