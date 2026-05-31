<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'MehfilCards'))</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('mehfil-theme') || 'day';
    </script>
    <link href="{{ asset('css/mehfilcards.css') }}" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="contact-ribbon">
            <span><i class="fa-brands fa-whatsapp"></i> +91 8009030734</span>
            <span><i class="fa-solid fa-envelope"></i> rizwan.creativeswork@gmail.com</span>
            <span><i class="fa-solid fa-location-dot"></i> Digital invitation platform</span>
        </div>

        <div class="topbar">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark">M</span>
                <span>MehfilCards</span>
            </a>
            <nav class="nav-actions" aria-label="Main navigation">
                <div class="nav-primary">
                    <a class="{{ request()->routeIs('home') ? 'nav-link-active' : '' }}" href="{{ route('home') }}"><span>Home</span></a>
                    <a href="{{ route('home') }}#maker"><span>Create Card</span></a>
                    <div class="dropdown">
                        <button class="nav-link-button dropdown-toggle" data-bs-toggle="dropdown" type="button">Packages</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('home') }}#maker">Self Service</a>
                            <a class="dropdown-item" href="{{ route('home') }}#maker">Self Plus</a>
                            <a class="dropdown-item" href="{{ route('home') }}#maker">Custom Event</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="nav-link-button dropdown-toggle" data-bs-toggle="dropdown" type="button">Categories</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('home') }}#maker">Eid</a>
                            <a class="dropdown-item" href="{{ route('home') }}#maker">Ramzan</a>
                            <a class="dropdown-item" href="{{ route('home') }}#maker">Walima</a>
                            <a class="dropdown-item" href="{{ route('home') }}#maker">Shadi</a>
                            <a class="dropdown-item" href="{{ route('home') }}#maker">Party</a>
                            <a class="dropdown-item" href="{{ route('home') }}#maker">All Festivals</a>
                        </div>
                    </div>
                    <a href="{{ route('home') }}#designs"><span>Designs</span></a>
                    <a href="{{ route('home') }}#contact"><span>Contact</span></a>
                </div>
                <div class="nav-secondary">
                    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch night mode">
                        <i class="fa-solid fa-moon"></i>
                        <span>Night</span>
                    </button>
                    <a class="nav-icon-link {{ request()->routeIs('payments') ? 'nav-link-active' : '' }}" href="{{ route('payments') }}" title="Payments"><i class="fa-solid fa-credit-card"></i><span>Payment</span></a>
                    <a class="nav-icon-link {{ request()->routeIs('scanner') ? 'nav-link-active' : '' }}" href="{{ route('scanner') }}" title="Scanner"><i class="fa-solid fa-qrcode"></i><span>Scan QR</span></a>
                    @auth
                        <a class="nav-admin-link {{ request()->routeIs('admin') ? 'nav-link-active' : '' }}" href="{{ route('admin') }}"><i class="fa-solid fa-sliders"></i><span>Admin</span></a>
                        <form method="POST" action="{{ route('logout') }}" class="nav-logout">
                            @csrf
                            <button type="submit"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></button>
                        </form>
                    @else
                        <a class="nav-login-link {{ request()->routeIs('login') ? 'nav-link-active' : '' }}" href="{{ route('login') }}"><i class="fa-solid fa-lock"></i><span>Admin Login</span></a>
                        <a class="nav-register-link {{ request()->routeIs('register') ? 'nav-link-active' : '' }}" href="{{ route('register') }}"><i class="fa-solid fa-user-plus"></i><span>Register</span></a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <section class="newsletter-band">
            <div>
                <h2>Join the MehfilCards list</h2>
                <p>Get new invitation designs, festival templates, and event updates first.</p>
            </div>
            <form class="newsletter-form" method="POST" action="{{ route('subscribe') }}">
                @csrf
                <input class="form-control" name="email" type="email" placeholder="Email address" required>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i><span>Subscribe</span></button>
            </form>
            @if (session('newsletter_status'))
                <div class="newsletter-status"><i class="fa-solid fa-circle-check"></i><span>{{ session('newsletter_status') }}</span></div>
            @endif
        </section>

        <section class="footer-grid">
            <div>
                <a class="brand footer-brand" href="{{ route('home') }}">
                    <span class="brand-mark">M</span>
                    <span>MehfilCards</span>
                </a>
                <p>MehfilCards is a complete digital invitation and greeting card platform for family events, festivals, and business celebrations.</p>
            </div>
            <div>
                <h3>Contact</h3>
                <a href="https://wa.me/918009030734"><i class="fa-brands fa-whatsapp"></i> +91 8009030734</a>
                <a href="mailto:rizwan.creativeswork@gmail.com"><i class="fa-solid fa-envelope"></i> rizwan.creativeswork@gmail.com</a>
                <span><i class="fa-solid fa-location-dot"></i> India / Online service</span>
            </div>
            <div>
                <h3>Help</h3>
                <a href="{{ route('demo') }}">Invite demo</a>
                <a href="{{ route('scanner') }}">QR verification</a>
                <a href="{{ route('login') }}">Admin login</a>
                <a href="{{ route('admin') }}">Manage designs</a>
                <a href="{{ route('home') }}#maker">Create card</a>
            </div>
            <div>
                <h3>Payments</h3>
                <div class="payment-row">
                    <a href="{{ route('payments') }}">UPI</a>
                    <a href="{{ route('payments') }}">Visa</a>
                    <a href="{{ route('payments') }}">Mastercard</a>
                    <a href="{{ route('payments') }}">Bank</a>
                </div>
            </div>
        </section>
        <div class="footer-bottom">All rights reserved | MehfilCards © 2026</div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const button = document.querySelector('[data-theme-toggle]');
            const icon = button?.querySelector('i');
            const label = button?.querySelector('span');

            function setTheme(theme) {
                document.documentElement.dataset.theme = theme;
                localStorage.setItem('mehfil-theme', theme);
                if (!button || !icon || !label) {
                    return;
                }
                const night = theme === 'night';
                icon.className = night ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                label.textContent = night ? 'Day' : 'Night';
                button.setAttribute('aria-label', night ? 'Switch day mode' : 'Switch night mode');
            }

            setTheme(localStorage.getItem('mehfil-theme') || 'day');
            button?.addEventListener('click', () => {
                setTheme(document.documentElement.dataset.theme === 'night' ? 'day' : 'night');
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
