@extends('layout')

@section('content')
<div class="container my-5">
    <h2>Pay with Visa</h2>
    <p>Total: {{ $order->amount }} EGP</p>

    <form id="payment-form">
        <div id="card-element"><!--Stripe.js injects Card Element--></div>
        <button id="submit" class="btn btn-dark mt-3">Pay</button>
    </form>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("{{ config('services.stripe.key') }}");

    const elements = stripe.elements();
    const card = elements.create("card");
    card.mount("#card-element");

    document.getElementById("payment-form").addEventListener("submit", async (e) => {
        e.preventDefault();
        const {
            paymentIntent,
            error
        } = await stripe.confirmCardPayment(
            "{{ $clientSecret }}", {
                payment_method: {
                    card: card
                }
            }
        );
        if (error) {
            alert(error.message);
        } else if (paymentIntent.status === "succeeded") {
            // Call backend to update order status
            await fetch("{{ route('checkout.confirm', $order->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
            });

            // Redirect to success page
            window.location.href = "{{ route('checkout.success') }}";
        }
    });
</script>

@endsection