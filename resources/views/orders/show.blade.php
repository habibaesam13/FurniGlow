@extends('layout')
<link href="{{ asset('css/orders.css') }}" rel="stylesheet">
@section('content')
<div class="container my-4">
    <h2 class="mb-4 text-center mb-2">Order Details</h2>

    <div class="row g-2 w-75">
        <!-- Order Info -->
        <div class="p-3 shadow-sm d-flex justify-content-between align-items-center order-header">
            <h5 class="fw-bold mb-0 order-id">Order Number: #{{ $order->id }}</h5>
            <span class="badge text-success">{{ ucfirst($order->status) }}</span>
        </div>
    </div>

    <!-- Order Items -->
    <div class="col-lg-8">
        <div class="cart-items">
            @foreach($order->orderItems as $item)
            <div class="cart-item d-flex align-items-start py-3 position-relative border-bottom">
                <!-- Product Image -->
                <img src="{{ asset('storage/'.$item->product->img) }}"
                    class="cart-item-img me-3 rounded"
                    alt="{{ $item->product->name }}"
                    style="width: 80px; height: 80px; object-fit: cover;">

                <!-- Product Details -->
                <div class="flex-grow-1">
                    <h5 class="product-name mb-1">
                        {{ $item->product->name }}
                        <span class="badge bg-secondary">
                            <a href="{{ route('category-products', $item->product->category->id) }}" class="text-white text-decoration-none">
                                {{ $item->product->category->name ?? '' }}
                            </a>
                        </span>
                    </h5>
                    <p class="fw-bold mb-2" style="font-size: 1.25rem;color: #333;">
                        ${{ number_format($item->price, 2) }}
                    </p>
                    <p class="mb-1 fw-semibold">Quantity: {{ $item->quantity }}</p>
                    <p class="fw-bold text-dark">Subtotal: ${{ number_format(round($item->total), 2) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <a href="{{ route('orders.index') }}" class="btn btn-dark w-25 rounded-pill py-2 mt-4 ">Back to Orders History</a>
        @if($order->status == "pending")
        <a href="{{ route('checkout.show',$order) }}"  class="btn btn-dark w-25 rounded-pill py-2 mt-4 ">Checkout</a>
        @endif
    </div>
</div>
</div>
@endsection