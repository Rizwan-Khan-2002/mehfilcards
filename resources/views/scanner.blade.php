@extends('layouts.app')

@section('title', 'QR Scanner - MehfilCards')

@section('content')
<section class="scanner-shell">
    <div class="scanner-box">
        <div class="section-title">
            <span>QR Scanner</span>
            <button class="icon-link" id="startScanner" title="Start camera"><i class="fa-solid fa-camera"></i></button>
        </div>
        <div id="reader"></div>
        <form id="verifyForm" class="manual-verify">
            <label>
                <span>Invite Code / Link</span>
                <input class="form-control" id="payload" placeholder="MF-DEMO26 or full invite link" value="MF-DEMO26">
            </label>
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-circle-check"></i><span>Verify</span></button>
        </form>
    </div>
    <aside class="verify-result" id="verifyResult">
        <span class="code-pill">Waiting</span>
        <h1>Scan an invite</h1>
        <div class="detail-list muted">
            <p><i class="fa-solid fa-qrcode"></i><span>QR data will appear here after verification.</span></p>
        </div>
    </aside>
</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const token = document.querySelector('meta[name="csrf-token"]').content;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

async function verifyPayload(payload) {
    const response = await fetch('{{ route('verify') }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
        body: JSON.stringify({payload})
    });
    const result = await response.json();
    renderResult(result);
}

function renderResult(result) {
    const box = $('#verifyResult');
    if (!result.ok) {
        box.html(`<span class="code-pill danger">Invalid</span><h1>Not verified</h1><div class="detail-list"><p><i class="fa-solid fa-triangle-exclamation"></i><span>${result.message}</span></p></div>`);
        return;
    }
    const d = result.data;
    box.html(`
        <span class="code-pill success">${escapeHtml(d.code)}</span>
        <h1>${escapeHtml(d.event_name)}</h1>
        <div class="detail-list">
            <p><i class="fa-solid fa-user"></i><span>${escapeHtml(d.guest_name)}</span></p>
            <p><i class="fa-solid fa-calendar-day"></i><span>${escapeHtml(`${d.date || ''} ${d.time || ''}`.trim())}</span></p>
            <p><i class="fa-solid fa-location-dot"></i><span>${escapeHtml(d.venue)}</span></p>
            <p><i class="fa-solid fa-hand-holding-heart"></i><span>Hosted by ${escapeHtml(d.host_name)}</span></p>
            <p><i class="fa-solid fa-tags"></i><span>${escapeHtml(d.occasion)}</span></p>
            <p><i class="fa-solid fa-chart-simple"></i><span>Scan count: ${escapeHtml(d.scans)}</span></p>
        </div>
        <a class="btn btn-outline-dark mt-3" href="${escapeHtml(d.invite_url)}"><i class="fa-solid fa-up-right-from-square"></i><span>Open Invite</span></a>
    `);
}

$('#verifyForm').on('submit', function (event) {
    event.preventDefault();
    verifyPayload($('#payload').val());
});

$('#startScanner').on('click', async function () {
    const scanner = new Html5Qrcode('reader');
    await scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 260 }, (decodedText) => {
        $('#payload').val(decodedText);
        verifyPayload(decodedText);
        scanner.stop();
    });
});
</script>
@endpush
