<?php

namespace App\Http\Controllers;

use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Invoice;

class InvoiceDemoController extends Controller
{
    public function show()
    {
        $buyer = new Buyer([
            'name' => 'Cliente Demo',
            'address' => 'Calle Ejemplo 123, Alicante',
            'phone' => '600 000 000',
            'custom_fields' => [
                'email' => 'cliente@demo.es',
                'cif' => 'B12345678',
            ],
        ]);

        $item = (new InvoiceItem())
            ->title('Desarrollo web')
            ->description('Landing page responsive + formulario de contacto')
            ->pricePerUnit(450.00)
            ->quantity(1)
            ->tax(21);

        $invoice = Invoice::make()
            ->series('DEM')
            ->sequence(1)
            ->buyer($buyer)
            ->date(now())
            ->currencySymbol('€')
            ->currencyCode('EUR')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyThousandsSeparator('.')
            ->currencyDecimalPoint(',')
            ->addItem($item)
            ->notes('¡Gracias por confiar en nosotros!');

        return $invoice->stream('factura-demo.pdf');
    }
}
