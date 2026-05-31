(function () {
    const templates = window.MEHFIL_TEMPLATES || [];
    const canvas = document.getElementById('cardCanvas');
    if (!canvas || !templates.length) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const strip = document.getElementById('templateStrip');
    const categorySelect = document.getElementById('categorySelect');
    const templateInput = document.getElementById('cardTemplateId');
    const nameCounter = document.getElementById('nameCounter');
    const qrOverlay = document.getElementById('liveQrOverlay');
    let currentTemplate = templates[0];
    let drawVersion = 0;

    const greetings = {
        eid: 'Eid Mubarak',
        ramzan: 'Ramzan Kareem',
        ramadan: 'Ramadan Kareem',
        dawat: 'Aapki Dawat Hai',
        walima: 'Walima Mubarak',
        shadi: 'Shadi Mubarak',
        wedding: 'Wedding Invitation',
        party: 'You Are Invited',
        festivals: 'Festival Greetings',
        holi: 'Happy Holi',
        diwali: 'Happy Diwali',
        christmas: 'Merry Christmas',
        'new year': 'Happy New Year',
        mehandi: 'Mehandi Mubarak',
        engagement: 'Engagement Mubarak',
        anniversary: 'Happy Anniversary',
        birthday: 'Happy Birthday',
        corporate: 'You Are Invited',
        aqeeqah: 'Aqeeqah Mubarak'
    };

    function field(id) {
        return document.getElementById(id);
    }

    function activeOccasion() {
        const manual = field('manualOccasion').value.trim();
        return manual || categorySelect.value || 'Invitation';
    }

    function setTemplate(template) {
        currentTemplate = template;
        templateInput.value = template.id;
        $('.template-option').removeClass('active');
        $(`.template-option[data-id="${template.id}"]`).addClass('active');
        draw();
    }

    function renderTemplates() {
        const category = categorySelect.value;
        const filtered = category === 'Custom'
            ? []
            : templates.filter((template) => template.category && template.category.name === category);
        const list = filtered.length ? filtered : templates;
        strip.innerHTML = list.map((template) => `
            <button type="button" class="template-option ${template.id === currentTemplate.id ? 'active' : ''}" data-id="${template.id}">
                <img src="${template.image_url || `/card-art/${template.slug}.svg`}" alt="${template.name}">
                <span>${template.name}</span>
            </button>
        `).join('');
        strip.querySelectorAll('.template-option').forEach((button) => {
            button.addEventListener('click', () => {
                const picked = templates.find((template) => String(template.id) === button.dataset.id);
                if (picked) {
                    setTemplate(picked);
                }
            });
        });
        if (!list.some((template) => template.id === currentTemplate.id)) {
            setTemplate(list[0]);
        }
    }

    function wrapText(text, maxWidth) {
        const words = String(text || '').split(/\s+/).filter(Boolean);
        const lines = [];
        let line = '';

        words.forEach((word) => {
            const candidate = `${line} ${word}`.trim();
            if (line && ctx.measureText(candidate).width > maxWidth) {
                lines.push(line);
                line = word;
            } else {
                line = candidate;
            }
        });

        if (line) {
            lines.push(line);
        }

        return lines.length ? lines : [''];
    }

    function centerText(text, y, size, color, weight, maxWidth) {
        ctx.font = `${weight || 700} ${size}px Georgia, serif`;
        ctx.textAlign = 'center';
        ctx.fillStyle = color;
        ctx.shadowColor = 'rgba(0,0,0,.45)';
        ctx.shadowBlur = 10;
        ctx.shadowOffsetY = 3;
        const lines = wrapText(text, maxWidth || 900);
        const lineHeight = size * 1.22;
        const start = y - ((lines.length - 1) * lineHeight / 2);
        lines.forEach((line, index) => ctx.fillText(line, 540, start + (index * lineHeight)));
        ctx.shadowBlur = 0;
        ctx.shadowOffsetY = 0;
    }

    function formatDate(dateValue) {
        if (!dateValue) {
            return '';
        }
        const date = new Date(`${dateValue}T00:00:00`);
        return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatTime(timeValue) {
        if (!timeValue) {
            return '';
        }
        const [hour, minute] = timeValue.split(':').map(Number);
        const date = new Date();
        date.setHours(hour, minute || 0, 0);
        return date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
    }

    function fallbackGreeting(occasion) {
        return greetings[occasion.toLowerCase()] || `${occasion} Invitation`;
    }

    function base64UrlEncode(value) {
        const bytes = new TextEncoder().encode(value);
        let binary = '';
        bytes.forEach((byte) => {
            binary += String.fromCharCode(byte);
        });

        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
    }

    function previewPayload(occasion) {
        const payload = {
            guest_name: field('guestName').value || 'Guest Name',
            host_name: field('hostName').value || 'Host Name',
            event_name: field('eventName').value || `${occasion} Celebration`,
            occasion,
            date: formatDate(field('eventDate').value),
            time: formatTime(field('eventTime').value),
            venue: field('venue').value || 'Venue',
            whatsapp: field('whatsapp').value || '',
            message: field('message').value || ''
        };

        return `MEHFIL-PREVIEW:${base64UrlEncode(JSON.stringify(payload))}`;
    }

    function drawQr(occasion, version) {
        const x = currentTemplate.qr_x || 72;
        const y = currentTemplate.qr_y || 72;
        const qrSize = 180;
        const qrSrc = `${window.MEHFIL_QR_PREVIEW_URL}?payload=${encodeURIComponent(previewPayload(occasion))}`;
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x - 12, y - 12, qrSize + 24, qrSize + 24);
        if (qrOverlay) {
            qrOverlay.src = qrSrc;
        }

        const qrImage = new Image();
        qrImage.onload = () => {
            if (version !== drawVersion) {
                return;
            }
            ctx.drawImage(qrImage, x, y, qrSize, qrSize);
        };
        qrImage.src = qrSrc;
    }

    function draw() {
        drawVersion += 1;
        const version = drawVersion;
        const image = new Image();
        image.onload = () => {
            if (version !== drawVersion) {
                return;
            }
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
            const theme = currentTemplate.theme || {};
            const accent = theme.accent || '#d8b45f';
            const second = theme.second || '#fff8e8';
            const occasion = activeOccasion();
            const heading = field('customGreeting').value.trim() || fallbackGreeting(occasion);
            centerText(heading, currentTemplate.greeting_y || 980, 52, accent, 700, 900);
            centerText(field('guestName').value || 'Guest Name', currentTemplate.name_y || 1070, 66, '#ffffff', 700, 920);
            centerText(`Hosted by ${field('hostName').value || 'Host Name'}`, currentTemplate.host_y || 1140, 31, second, 500, 900);
            centerText(field('eventName').value || `${occasion} Celebration`, 1240, 34, second, 500, 920);
            centerText(`${formatDate(field('eventDate').value)} ${formatTime(field('eventTime').value)}`.trim(), 1294, 27, second, 500, 900);
            centerText(field('venue').value || 'Venue', 1346, 24, second, 500, 940);
            drawQr(occasion, version);
        };
        image.src = currentTemplate.image_url || `/card-art/${currentTemplate.slug}.svg`;
    }

    function syncOccasion() {
        const occasion = activeOccasion();
        if (!field('customGreeting').dataset.touched) {
            field('customGreeting').value = fallbackGreeting(occasion);
        }
        draw();
    }

    function updateNameCounter() {
        const value = field('guestName').value || '';
        if (nameCounter) {
            nameCounter.textContent = value.length;
        }
        field('guestName').classList.toggle('is-invalid', value.length > 25);
    }

    $('#customGreeting').on('input', function () {
        this.dataset.touched = '1';
    });

    $('.live-field').on('input change', function () {
        updateNameCounter();
        draw();
    });
    $('#manualOccasion').on('input', syncOccasion);
    $('#categorySelect').on('change', function () {
        field('customGreeting').dataset.touched = '';
        renderTemplates();
        syncOccasion();
    });

    renderTemplates();
    updateNameCounter();
    setTemplate(currentTemplate);
})();
