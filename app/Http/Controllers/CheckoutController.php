<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct() {}

    public function show(Order $order, Request $request)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        // Order should have client_secret (created in OrderService)
        if (empty($order->client_secret) && $order->payment_method == 'visa') {
            return redirect()->route('cart.index')->with('error', 'Payment not initialized for this order.');
        } elseif ($order->payment_method == 'cash') {
            $order->status = 'confirmed';
            $order->update();
            return redirect()->back()->with('success', 'Order checked out successfully.');
        }
        $clientSecret = $order->client_secret;
        return view('checkout.show', compact('order', 'clientSecret'));
    }
    public function confirm($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->status = 'confirmed';
        $order->save();

        return response()->json(['success' => true]);
    }
}
