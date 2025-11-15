@extends('layout')
<link href="{{ asset('css/orders.css') }}" rel="stylesheet">
@section('title', 'My Orders')

@section('content')
<div class="container my-4">
    <h2 class="mb-3 text-center">My Orders</h2>
    @if($ordersByMonth->isEmpty())
    <div class="no-order align-items-center text-center py-5 m w-50 mx-auto">
        <i class="fa-solid fa-receipt"></i>
        <p class="main">No orders yet</p>
        <p class="text-muted">Browse Products and start ordering</p>
        <p class="text-muted">Delivery is free for orders over 2000 EGP</p>
        <a href="{{ route('products.index') }}" class="btn btn-dark rounded-pill px-5 mt-4">
            Shop Now
        </a>
    </div>
        
    @else
        @foreach($ordersByMonth as $month => $orders)
            <h4 class="mt-4 p-2 d-flex justify-content-between align-items-center">{{ $month }} <small class="text-muted">{{ $orders->count() }} Orders</small></h4>
            @foreach($orders as $order)
                @php $items = json_decode($order->items, true); @endphp
                <div class="card mb-3 shadow-sm w-75 position-relative">
                    <!-- Move the stretched link inside card-body -->
                    <div class="card-body">
                        <a href="{{ route('orders.show', $order) }}" class="stretched-link"></a>
                        
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">Order #{{ $order->id }}</h6>
                            <small class="mt-4">{{ $order->created_at->format('M d, Y | H:i') }}</small>
                        </div>

                        {{-- Products preview --}}
                        <div class="d-flex align-items-center my-2">
                            @foreach(array_slice($items, 0, 5) as $item)
                                <img src="{{ "storage/".$item['image'] ?? asset('images/default.png') }}" 
                                     alt="{{ $item['name'] }}" 
                                     class="rounded me-1" 
                                     width="40" height="40">
                            @endforeach
                            @if(count($items) > 5)
                                <span class="badge bg-secondary">+{{ count($items) - 5 }}</span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between">
                            <span><strong>{{ count($items) }}</strong> Products</span>
                            <span><strong>{{ number_format($order->amount, 2) }}</strong> EGP</span>
                        </div>
                    </div>
                    @if($order->status == 'pending')
                    <form action="{{ route('orders.destroy', $order) }}" method="POST"
                          class="position-absolute top-0 end-0 m-2"
                          style="z-index: 10;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-sm btn-light text-danger rounded-circle shadow-sm position-relative" 
                                title="Remove">
                            <i class="fa-solid fa-trash" style="font-size: 1rem;"></i>
                        </button>
                    </form>
                    @endif
                </div>
            @endforeach
        @endforeach
    @endif
</div>
@endsection