@extends('layouts.app')

@section('title', 'MehfilCards - Create Invitation')

@section('content')
<section class="portal-hero" id="about">
    <div class="hero-copy">
        <span class="eyebrow">Digital invitations for every mehfil</span>
        <h1>MehfilCards is a complete platform for invitations, greetings, and event QR verification.</h1>
        <p>Create Eid, Ramzan, Dawat, Walima, Shadi, party, and festival cards from one place. Choose a design, enter guest details, generate a shareable invite, and verify guests by QR scan.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="#maker"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Create Card</span></a>
            <a class="btn btn-outline-dark" href="{{ route('demo') }}"><i class="fa-solid fa-play"></i><span>View Demo</span></a>
        </div>
    </div>
    <div class="hero-panel">
        <div class="mini-stat">
            <strong>18+</strong>
            <span>Occasions</span>
        </div>
        <div class="mini-stat">
            <strong>QR</strong>
            <span>Verification</span>
        </div>
        <div class="mini-stat">
            <strong>PNG</strong>
            <span>Download</span>
        </div>
    </div>
</section>

<section class="service-strip" id="why">
    <article>
        <i class="fa-solid fa-layer-group"></i>
        <h2>Design library</h2>
        <p>Ready cards for Eid, Walima, Shadi, festivals, birthdays, corporate events and custom occasions.</p>
    </article>
    <article>
        <i class="fa-solid fa-qrcode"></i>
        <h2>QR guest scan</h2>
        <p>Every invite gets a real QR link that opens stored invitation data during verification.</p>
    </article>
    <article>
        <i class="fa-solid fa-sliders"></i>
        <h2>Admin control</h2>
        <p>Add categories and upload new templates from the admin panel without touching code.</p>
    </article>
</section>

<section class="public-contact" id="contact">
    <div>
        <span class="eyebrow">Contact MehfilCards</span>
        <h2>Client yahin se directly contact kar sakta hai.</h2>
        <p>Custom invitation, QR verification, payment setup, aur festival/event cards ke liye WhatsApp ya email par message bhejiye.</p>
    </div>
    <div class="contact-actions">
        <a class="contact-card" href="https://wa.me/918009030734">
            <i class="fa-brands fa-whatsapp"></i>
            <span>WhatsApp</span>
            <strong>+91 8009030734</strong>
        </a>
        <a class="contact-card" href="mailto:rizwan.creativeswork@gmail.com">
            <i class="fa-solid fa-envelope"></i>
            <span>Email</span>
            <strong>rizwan.creativeswork@gmail.com</strong>
        </a>
        <a class="contact-card" href="{{ route('payments') }}">
            <i class="fa-solid fa-credit-card"></i>
            <span>Payment</span>
            <strong>UPI / Bank details</strong>
        </a>
    </div>
</section>

<section class="workspace-shell greeting-workspace" id="maker">
    <div class="creator-panel hayhom-maker">
        <div class="section-title">
            <span>MehfilCards Greeting Cards</span>
            <a href="{{ route('admin') }}" class="icon-link" title="Manage templates"><i class="fa-solid fa-gear"></i></a>
        </div>

        <div class="greeting-intro">
            <h1>Create your greeting card</h1>
            <p>Enter the recipient name, choose an occasion, then select a design to prepare your card.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger compact-alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="inviteForm" method="POST" action="{{ route('invitations.store') }}">
            @csrf
            <input type="hidden" name="card_template_id" id="cardTemplateId" value="{{ $templates->first()?->id }}">

            <label class="recipient-field">
                <span>Recipient name</span>
                <input class="form-control live-field" name="guest_name" id="guestName" value="Ayaan Family" maxlength="25" required>
                <small><span id="nameCounter">12</span> / 25</small>
            </label>

            <p class="limit-note">English names are limited to 20 characters. Arabic/Urdu/Hindi names are limited to 25 characters.</p>

            <div class="field-grid two occasion-row">
                <label>
                    <span>Design category</span>
                    <select class="form-select" id="categorySelect" name="occasion">
                        <option value="Custom">Custom / Type manually</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Write any occasion</span>
                    <input class="form-control" id="manualOccasion" name="manual_occasion" list="occasionSuggestions" placeholder="Ramzan Dawat, Walima, House Party">
                    <small class="field-hint">Yahan kuch bhi likhiye, card par wahi occasion show hoga.</small>
                </label>
            </div>
            <datalist id="occasionSuggestions">
                @foreach ($categories as $category)
                    <option value="{{ $category->name }}"></option>
                @endforeach
                <option value="Shop Opening"></option>
                <option value="Naming Ceremony"></option>
                <option value="House Warming"></option>
                <option value="Office Party"></option>
                <option value="School Event"></option>
                <option value="Baby Shower"></option>
                <option value="Reception"></option>
                <option value="Nikah"></option>
                <option value="Sangeet"></option>
            </datalist>

            <div class="field-grid two">
                <label>
                    <span>Card Heading</span>
                    <input class="form-control live-field" name="custom_greeting" id="customGreeting" value="Eid Mubarak">
                </label>
                <label>
                    <span>Language</span>
                    <select class="form-select live-field" name="language_mode" id="languageMode">
                        <option value="english">English</option>
                        <option value="hindi">Hindi</option>
                        <option value="urdu">Urdu</option>
                        <option value="mixed" selected>Mixed</option>
                    </select>
                </label>
            </div>

            <div class="design-title" id="designs">
                <span>Select a card design</span>
                <small>Choose one design, then create your invite.</small>
            </div>
            <div class="template-strip greeting-designs" id="templateStrip"></div>

            <details class="advanced-details">
                <summary>Event details</summary>
                <div class="advanced-grid">
                    <div class="field-grid two">
                        <label>
                            <span>Host Name</span>
                            <input class="form-control live-field" name="host_name" id="hostName" value="Khan Family" required>
                        </label>
                        <label>
                            <span>Event Name</span>
                            <input class="form-control live-field" name="event_name" id="eventName" value="Eid Celebration Dinner" required>
                        </label>
                    </div>

                    <div class="field-grid two">
                        <label>
                            <span>Date</span>
                            <input class="form-control live-field" type="date" name="event_date" id="eventDate" value="{{ $defaultDate }}" required>
                        </label>
                        <label>
                            <span>Time</span>
                            <input class="form-control live-field" type="time" name="event_time" id="eventTime" value="19:30">
                        </label>
                    </div>

                    <label>
                        <span>Venue</span>
                        <input class="form-control live-field" name="venue" id="venue" value="Royal Banquet Hall, Mumbai" required>
                    </label>

                    <div class="field-grid two">
                        <label>
                            <span>WhatsApp</span>
                            <input class="form-control" name="whatsapp" id="whatsapp" value="+91 98765 43210">
                        </label>
                        <label>
                            <span>Message</span>
                            <input class="form-control live-field" name="message" id="message" value="Your presence will make our celebration complete.">
                        </label>
                    </div>
                </div>
            </details>

            <div class="action-row">
                <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-download"></i>
                    <span>Create & Download</span>
                </button>
                <a class="btn btn-outline-dark" href="{{ route('demo') }}">
                    <i class="fa-solid fa-play"></i>
                    <span>Invite Demo</span>
                </a>
            </div>
        </form>
    </div>

    <aside class="preview-panel">
        <div class="section-title">
            <span>Card Preview</span>
            <a href="{{ route('scanner') }}" class="icon-link" title="Open scanner"><i class="fa-solid fa-qrcode"></i></a>
        </div>
        <div class="canvas-frame">
            <canvas id="cardCanvas" width="1080" height="1440"></canvas>
            <img id="liveQrOverlay" class="live-qr-overlay" alt="Live QR preview">
        </div>
        <div class="premium-grid">
            <a href="{{ route('scanner') }}">
                <i class="fa-solid fa-shield-halved"></i>
                <span>QR Verify</span>
            </a>
            <a href="{{ route('admin') }}">
                <i class="fa-solid fa-layer-group"></i>
                <span>Templates</span>
            </a>
            <a href="{{ route('demo') }}">
                <i class="fa-solid fa-link"></i>
                <span>Invite Demo</span>
            </a>
        </div>
    </aside>
</section>
@endsection

@push('scripts')
<script>
    window.MEHFIL_TEMPLATES = @json($templates);
    window.MEHFIL_QR_PREVIEW_URL = @json(route('qr.preview'));
</script>
<script src="{{ asset('js/mehfilcards.js') }}"></script>
@endpush
