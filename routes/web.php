<?php

use App\Exports\PostsExport;
use App\Http\Controllers\PaymentPaypalController;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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



Route::get('/qr', function () {
    //QrCode::format('png')->generate('DesarrolloLibre');
    QrCode::format('png')->size(700)->color(255, 0, 0)
    // ->merge('/assets/img/logo.png', .3, true)
    ->generate('Desarrollo libre Andres', '../public/qrcode.png');
    return view('welcome');
});

Route::get('/paypal', [PaymentPaypalController::class, 'paypal']);
Route::post('/paypal-process-order/{order}', [PaymentPaypalController::class, 'paypalProcessOrder']);
