<?php

namespace App\Http\Controllers\Ai;

use App\Ai\Agents\Appointment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function schedule(Request $request): JsonResponse
    {
        $date = $request->input('date', 'Now');
        $message = $request->input('message', 'Appointment');

        $response = (new Appointment)->prompt("Schedule an appointment. Date: {$date}, Message: {$message}");

        return response()->json([
            'respuesta' => $response->text,
        ]);
    }

    public function list()
    {
        $files = glob(storage_path('app/appointments/*.json'));
        $appointments = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $appointments[] = json_decode($content, true);
        }

        return response()->json([
            'citas' => $appointments,
        ]);
    }
}
