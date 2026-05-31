@extends('layouts.app')

@section('title', $invitation->event_name.' - '.$invitation->code)

@section('content')
<section class="invite-shell">
    <div class="invite-card-view">
        <img src="{{ route('invite.download', $invitation) }}?inline=1" alt="{{ $invitation->event_name }}">
    </div>
    <aside class="invite-details">
        @if (session('created'))
            <div class="alert alert-success compact-alert">Invite ready: {{ $invitation->code }}</div>
        @endif
        <span class="code-pill">{{ $invitation->code }}</span>
        <h1>{{ $invitation->event_name }}</h1>
        <div class="detail-list">
            <p><i class="fa-solid fa-user"></i><span>{{ $invitation->guest_name }}</span></p>
            <p><i class="fa-solid fa-calendar-day"></i><span>{{ $invitation->event_date->format('d M Y') }} {{ $invitation->event_time ? \Carbon\Carbon::createFromFormat('H:i:s', $invitation->event_time)->format('h:i A') : '' }}</span></p>
            <p><i class="fa-solid fa-location-dot"></i><span>{{ $invitation->venue }}</span></p>
            <p><i class="fa-solid fa-hand-holding-heart"></i><span>Hosted by {{ $invitation->host_name }}</span></p>
        </div>
        <img class="qr-large" src="{{ $qrDataUri }}" alt="QR code">
        <div class="action-row stack">
            <a class="btn btn-primary" href="{{ route('invite.download', $invitation) }}"><i class="fa-solid fa-download"></i><span>Download Card</span></a>
            <button class="btn btn-outline-dark" id="copyInvite" data-link="{{ $inviteUrl }}"><i class="fa-solid fa-copy"></i><span>Copy Link</span></button>
            <a class="btn btn-outline-dark" href="{{ route('scanner') }}"><i class="fa-solid fa-qrcode"></i><span>Scan Verify</span></a>
        </div>
    </aside>
</section>
@endsection

@push('scripts')
<script>
$('#copyInvite').on('click', async function () {
    await navigator.clipboard.writeText($(this).data('link'));
    $(this).find('span').text('Copied');
});
</script>
@endpush
