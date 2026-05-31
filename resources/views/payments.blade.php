@extends('layouts.app')

@section('title', 'Payments - MehfilCards')

@section('content')
<section class="payments-shell">
    <div class="payments-hero">
        <span class="eyebrow">Secure booking</span>
        <h1>Payments for custom invitation work</h1>
        <p>Use UPI or WhatsApp to confirm your package. Online gateway integration can be connected later with Razorpay/Stripe when your business account is ready.</p>
    </div>

    <div class="payment-cards">
        <article>
            <i class="fa-brands fa-google-pay"></i>
            <h2>UPI Payment</h2>
            <p>UPI ID: <strong>{{ $upiId }}</strong></p>
            <a class="btn btn-primary" href="upi://pay?pa={{ urlencode($upiId) }}&pn=MehfilCards&cu=INR"><i class="fa-solid fa-indian-rupee-sign"></i><span>Pay by UPI</span></a>
        </article>
        <article>
            <i class="fa-brands fa-whatsapp"></i>
            <h2>WhatsApp Booking</h2>
            <p>{{ $phone }}</p>
            <a class="btn btn-outline-dark" href="https://wa.me/918009030734?text=Assalamualaikum%20MehfilCards%2C%20I%20want%20a%20custom%20invitation%20package."><i class="fa-brands fa-whatsapp"></i><span>Message Now</span></a>
        </article>
        <article>
            <i class="fa-solid fa-envelope-open-text"></i>
            <h2>Email Support</h2>
            <p>{{ $email }}</p>
            <a class="btn btn-outline-dark" href="mailto:{{ $email }}?subject=MehfilCards%20Custom%20Design"><i class="fa-solid fa-envelope"></i><span>Email Us</span></a>
        </article>
    </div>
</section>
@endsection
