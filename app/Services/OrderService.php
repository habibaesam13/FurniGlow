<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class OrderService
{
    public function createOrder($paymentMethod)
    {
        return DB::transaction(function () use ($paymentMethod) {
            $cart = Auth::user()->cart;

            if (!$cart || $cart->items->isEmpty()) {
                throw new \Exception("Cart is empty");
            }

            $order = Order::where('user_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if (!$order) {
                $order = Order::create([
                    'user_id'        => Auth::id(),
                    'total'          => 0,
                    'status'         => 'pending',
                    'items'          => json_encode([]),
                    'amount'         => 0,
                    'payment_method' => $paymentMethod,
                ]);
            }

            $existingItems = json_decode($order->items, true) ?? [];

            foreach ($cart->items as $cartItem) {
                $existingIndex = collect($existingItems)->search(fn($i) => $i['product_id'] == $cartItem->product_id);

                if ($existingIndex !== false) {

                    $existingItems[$existingIndex]['quantity'] += $cartItem->quantity;
                    $existingItems[$existingIndex]['total'] = $existingItems[$existingIndex]['quantity'] * $existingItems[$existingIndex]['price'];


                    $orderItem = $order->orderItems()->where('product_id', $cartItem->product_id)->first();
                    if ($orderItem) {
                        $newQuantity = $orderItem->quantity + $cartItem->quantity;
                        $newTotal    = $newQuantity * $orderItem->price;

                        $orderItem->update([
                            'quantity' => $newQuantity,
                            'total'    => $newTotal,
                        ]);
                    }
                } else {
                    $newItem = [
                        'product_id' => $cartItem->product_id,
                        'name'       => $cartItem->product->name,
                        'quantity'   => $cartItem->quantity,
                        'price'      => $cartItem->price,
                        'image'      => $cartItem->product->img,
                        'total'      => $cartItem->price * $cartItem->quantity,
                    ];
                    $existingItems[] = $newItem;

                    $order->orderItems()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity'   => $cartItem->quantity,
                        'price'      => $cartItem->price,
                        'total'      => $cartItem->price * $cartItem->quantity,
                    ]);
                }
            }

            // Recalculate total order amount
            $newTotal = collect($existingItems)->sum('total');

            // After updating $order
            $order->update([
                'items'          => json_encode($existingItems),
                'total'          => $newTotal,
                'amount'         => $newTotal,
                'payment_method' => $paymentMethod,
            ]);

            // Create Stripe PaymentIntent *only for visa*
            if ($paymentMethod === 'visa') {

                Stripe::setApiKey(config('services.stripe.secret'));

                $paymentIntent = PaymentIntent::create([
                    'amount' => $order->amount * 100,
                    'currency' => 'egp',
                    'automatic_payment_methods' => ['enabled' => true],
                    'metadata' => [
                        'order_id' => $order->id,
                        'user_id' => Auth::id(),
                    ],
                ]);
                //dd($paymentIntent);
                // Save to DB
                $order->update([
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret'     => $paymentIntent->client_secret,
                ]);
            }

            // Clear the cart
            $cart->items()->delete();

            return $order;
        });
    }

    public function getOrderById($orderId)
    {
        return Order::where('user_id', Auth::id())->with('orderItems')->findOrFail($orderId);
    }

    public function updateOrder($orderId, $data)
    {
        return DB::transaction(function () use ($orderId, $data) {
            $order = Order::where('user_id', Auth::id())->findOrFail($orderId);
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'status' => $data['status'] ?? $order->status,
            ]);

            return $order;
        });
    }
    public function deleteOrder($orderId)
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::where('user_id', Auth::id())->findOrFail($orderId);
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            $order->delete();
            return true;
        });
    }

    public function getUserOrders()
    {
        return Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($order) {
                return $order->created_at->format('F Y');
            });
    }
}
