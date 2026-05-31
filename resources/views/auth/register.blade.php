@extends('layouts.app')

@section('title', 'Register Admin - MehfilCards')

@section('content')
<section class="auth-shell">
    <div class="auth-split">
        <aside class="auth-visual">
            <span class="eyebrow">Start managing</span>
            <h1>Create an admin account for your MehfilCards panel.</h1>
            <div class="auth-feature-list">
                <span><i class="fa-solid fa-user-shield"></i> Secure access</span>
                <span><i class="fa-solid fa-cloud-arrow-up"></i> Upload templates</span>
                <span><i class="fa-solid fa-layer-group"></i> Design library</span>
            </div>
        </aside>
        <div class="auth-card">
        <span class="eyebrow">Create admin</span>
        <h1>Register account</h1>
        <p>This account can access the admin panel and manage custom card designs.</p>

        @if ($errors->any())
            <div class="alert alert-danger compact-alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label>
                <span>Name</span>
                <input class="form-control" name="name" value="{{ old('name') }}" required autofocus>
            </label>
            <label>
                <span>Email</span>
                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>
                <span>Password</span>
                <input class="form-control" type="password" name="password" required>
            </label>
            <label>
                <span>Confirm Password</span>
                <input class="form-control" type="password" name="password_confirmation" required>
            </label>
            <button class="btn btn-primary w-100" type="submit"><i class="fa-solid fa-user-shield"></i><span>Create Admin</span></button>
        </form>

        <div class="auth-switch">
            <span>Already registered?</span>
            <a href="{{ route('login') }}">Login</a>
        </div>
        </div>
    </div>
</section>
@endsection
