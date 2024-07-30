<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StripeController extends Controller
{
    public function checkout(Request $request)
    {
        \Stripe\Stripe::setApiKey(config('stripe.sk'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Order Total', // You can change the name as needed
                    ],
                    'unit_amount' => (int)($request->total_price),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
        ]);
        return response()->json(['id' => $session->id, 'url' => $session->url]);
    }
}
