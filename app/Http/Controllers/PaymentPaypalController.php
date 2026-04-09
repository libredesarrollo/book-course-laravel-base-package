<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class PaymentPaypalController extends Controller
{
    private $clientId;

    private $secret;

    private $baseURL = 'https://api-m.paypal.com';
    // private $baseURL = 'https://api-m.sandbox.paypal.com';

    public function __construct()
    {
        $this->baseURL =
            config('app')['env'] == 'local' ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->clientId =
            config('app')['paypal_id'];
        $this->secret =
            config('app')['paypal_secrect'];
    }

    public function paypal()
    {
        return view('paypal');
    }

    public function paypalProcessOrder(string $order)
    {
        // dd($order);
        $accessToken = $this->getAccessToken();

        $response = Http::acceptJson()->withToken($accessToken)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseURL."/v2/checkout/orders/$order/capture", [
            'application_context' => [
                'return_url' => 'http://larafirstepspackages.test/paypal',
                'cancel_url' => 'http://larafirstepspackages.test/paypal',
            ],
        ])->json();

        dd($response);
    }

    private function getAccessToken()
    {
        $response = Http::asForm()->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->withBasicAuth($this->clientId, $this->secret)
            ->post($this->baseURL.'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ])->json();

        return $response['access_token'];
    }
}
