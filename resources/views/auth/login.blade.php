@extends('layouts.app')

@section('title', 'Admin Login - MehfilCards')

@section('content')
<section class="auth-shell">
    <div class="auth-split">
        <aside class="auth-visual">
            <span class="eyebrow">MehfilCards Admin</span>
            <h1>Control your invitation business from one clean panel.</h1>
            <div class="auth-feature-list">
                <span><i class="fa-solid fa-tags"></i> Categories</span>
                <span><i class="fa-solid fa-palette"></i> Custom designs</span>
                <span><i class="fa-solid fa-qrcode"></i> QR verification</span>
            </div>
        </aside>
        <div class="auth-card">
        <span class="eyebrow">Secure login</span>
        <h1>Welcome back</h1>
        <p>Login to manage categories, upload custom designs, and maintain invitation templates.</p>

        @if (session('status'))
            <div class="alert alert-success compact-alert">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger compact-alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label>
                <span>Email</span>
                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>
                <span>Password</span>
                <input class="form-control" type="password" name="password" required>
            </label>
            <label class="checkbox-line">
                <input type="checkbox" name="remember" value="1">
                <span>Remember me</span>
            </label>
            <button class="btn btn-primary w-100" type="submit"><i class="fa-solid fa-right-to-bracket"></i><span>Login</span></button>
        </form>

        <div class="auth-switch">
            <span>No admin account?</span>
            <a href="{{ route('register') }}">Create one</a>
        </div>
        </div>
    </div>
</section>
@endsection
