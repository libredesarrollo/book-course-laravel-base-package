<?php

use App\Exports\PostsExport;
use App\Http\Controllers\PaymentPaypalController;
use Illuminate\Support\Facades\Route;
use Laravel\Ai\Enums\Lab;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use Laravel\AI;

use function Laravel\Ai\agent;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/is-mobile', function () {
    dd(isMobile());
    return view('welcome');
});
Route::get('/export-excel', function () {
    return Excel::download(new PostsExport, 'posts.xlsx');
});
Route::get('/laravel-ia-text', function () {

    // $response = agent(
    //     instructions: 'Eres un asistente experto en Laravel.',)->prompt(
    //     'Genera una lista de 3 temas de Laravel 13 en formato JSON',
    //     provider: Lab::Gemini,
    //     model: 'gemini-2.0-flash',);
    $response = agent(
        instructions: 'Eres un asistente experto en Laravel.',)->prompt(
        'Genera una lista de 3 temas de Laravel 13 en formato JSON',
         provider: 'openai', // ESTO ES VITAL
         model: 'gemma-3-12b-it-IQ4_XS'
        );

    // $response = AI::chat('Explícame qué es un Repository Pattern en Laravel')
    // ->model('llama3') // El nombre del modelo que tengas en local
    // ->send();
    
    dd($response);
});

Route::get('/laravel-ia-text2', function () {
    try {
        $response = agent(
            instructions: 'Eres un asistente experto en Laravel.',
        )->prompt(
            'Genera una lista de 3 temas de Laravel 13 en formato JSON',
            provider: 'openai',
            model: 'gemma-3-12b-it-IQ4_XS' // Copiado exactamente de tu JSON
        );

        dd($response);
    } catch (\Exception $e) {
        // Esto nos dirá qué está pasando realmente si falla
        return response()->json([
            'message' => $e->getMessage(),
            'check_jan' => 'Asegúrate de que el servidor en Jan.ia esté en "Started"'
        ], 500);
    }
});



Route::get('/qr', function () {
    //QrCode::format('png')->generate('DesarrolloLibre');
    QrCode::format('png')->size(700)->color(255, 0, 0)
    // ->merge('/assets/img/logo.png', .3, true)
    ->generate('Desarrollo libre Andres', '../public/qrcode.png');
    return view('welcome');
});

Route::get('/paypal', [PaymentPaypalController::class, 'paypal']);
Route::post('/paypal-process-order/{order}', [PaymentPaypalController::class, 'paypalProcessOrder']);
