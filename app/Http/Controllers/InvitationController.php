<?php

namespace App\Http\Controllers;

use App\Models\CardTemplate;
use App\Models\Category;
use App\Models\Invitation;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function home(): View
    {
        $categories = Category::with(['templates' => fn ($query) => $query->where('active', true)->orderBy('id')])
            ->orderBy('id')
            ->get();

        return view('home', [
            'categories' => $categories,
            'templates' => CardTemplate::with('category')->where('active', true)->orderBy('id')->get(),
            'defaultDate' => now()->addDays(7)->format('Y-m-d'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'card_template_id' => ['required', 'exists:card_templates,id'],
            'guest_name' => ['required', 'string', 'max:120'],
            'host_name' => ['required', 'string', 'max:120'],
            'event_name' => ['required', 'string', 'max:160'],
            'occasion' => ['required', 'string', 'max:80'],
            'manual_occasion' => ['nullable', 'string', 'max:80'],
            'custom_greeting' => ['nullable', 'string', 'max:120'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['required', 'string', 'max:220'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:420'],
            'language_mode' => ['required', 'in:english,hindi,urdu,mixed'],
        ]);

        $occasion = trim((string) ($validated['manual_occasion'] ?: $validated['occasion']));

        $invitation = Invitation::create([
            'card_template_id' => $validated['card_template_id'],
            'code' => $this->makeCode(),
            'guest_name' => $validated['guest_name'],
            'host_name' => $validated['host_name'],
            'event_name' => $validated['event_name'],
            'occasion' => $occasion,
            'custom_greeting' => $validated['custom_greeting'] ?: $this->defaultGreeting($occasion),
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'] ?? null,
            'venue' => $validated['venue'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'message' => $validated['message'] ?? null,
            'language_mode' => $validated['language_mode'],
        ]);

        return redirect()->route('invite.show', $invitation)->with('created', true);
    }

    public function show(Invitation $invitation): View
    {
        $invitation->load('template.category');

        return view('invite', [
            'invitation' => $invitation,
            'inviteUrl' => $this->publicRoute('invite.show', $invitation),
            'qrDataUri' => $this->qrDataUri($this->qrPayload($invitation), 300),
        ]);
    }

    public function demo(): RedirectResponse
    {
        $template = CardTemplate::where('slug', 'eid-royal-crescent')->first() ?? CardTemplate::first();

        $invitation = Invitation::firstOrCreate(
            ['code' => 'MF-DEMO26'],
            [
                'card_template_id' => $template?->id,
                'guest_name' => 'Ayaan Family',
                'host_name' => 'Khan Family',
                'event_name' => 'Eid Celebration Dinner',
                'occasion' => 'Eid',
                'custom_greeting' => 'Eid Mubarak',
                'event_date' => now()->addDays(10)->format('Y-m-d'),
                'event_time' => '19:30',
                'venue' => 'Royal Banquet Hall, Mumbai',
                'whatsapp' => '+91 98765 43210',
                'message' => 'Your presence will make our celebration complete.',
                'language_mode' => 'mixed',
            ]
        );

        return redirect()->route('invite.show', $invitation);
    }

    public function scanner(): View
    {
        return view('scanner');
    }

    public function payments(): View
    {
        return view('payments', [
            'upiId' => 'mehfilcards@upi',
            'phone' => '+91 8009030734',
            'email' => 'rizwan.creativeswork@gmail.com',
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:160'],
        ]);

        DB::table('subscribers')->updateOrInsert(
            ['email' => Str::lower($validated['email'])],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('newsletter_status', 'Thank you. We will contact you with new MehfilCards updates.');
    }

    public function verify(Request $request): JsonResponse
    {
        $payload = trim((string) $request->input('payload', ''));
        $preview = $this->decodePreviewPayload($payload);

        if ($preview) {
            return response()->json([
                'ok' => true,
                'message' => 'Preview invite verified.',
                'data' => [
                    'code' => 'PREVIEW',
                    'guest_name' => $preview['guest_name'] ?? 'Guest Name',
                    'host_name' => $preview['host_name'] ?? 'Host Name',
                    'event_name' => $preview['event_name'] ?? 'Event',
                    'occasion' => $preview['occasion'] ?? 'Invitation',
                    'date' => $preview['date'] ?? null,
                    'time' => $preview['time'] ?? null,
                    'venue' => $preview['venue'] ?? 'Venue',
                    'whatsapp' => $preview['whatsapp'] ?? null,
                    'message' => $preview['message'] ?? null,
                    'scans' => 'Preview',
                'invite_url' => $this->publicRoute('home').'#maker',
                ],
            ]);
        }

        $code = $this->extractCode($payload);

        if (!$code) {
            return response()->json([
                'ok' => false,
                'message' => 'Valid invite code ya invite link nahi mila.',
            ], 422);
        }

        $invitation = Invitation::with('template.category')->where('code', $code)->first();

        if (!$invitation) {
            return response()->json([
                'ok' => false,
                'message' => 'Invitation record database mein nahi mila.',
            ], 404);
        }

        DB::table('invitation_scans')->insert([
            'invitation_id' => $invitation->id,
            'scanned_payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invitation->increment('scan_count');
        $invitation->forceFill(['last_scanned_at' => now()])->save();

        return response()->json([
            'ok' => true,
            'message' => 'Invite verified successfully.',
            'data' => [
                'code' => $invitation->code,
                'guest_name' => $invitation->guest_name,
                'host_name' => $invitation->host_name,
                'event_name' => $invitation->event_name,
                'occasion' => $invitation->occasion,
                'date' => $invitation->event_date?->format('d M Y'),
                'time' => $invitation->event_time ? Carbon::createFromFormat('H:i:s', $invitation->event_time)->format('h:i A') : null,
                'venue' => $invitation->venue,
                'whatsapp' => $invitation->whatsapp,
                'message' => $invitation->message,
                'scans' => $invitation->scan_count,
                'invite_url' => $this->publicRoute('invite.show', $invitation),
            ],
        ]);
    }

    public function qr(Invitation $invitation): Response
    {
        $result = $this->qrResult($this->qrPayload($invitation), 520);

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function previewQr(Request $request): Response
    {
        $payload = trim((string) $request->query('payload', ''));
        if ($payload === '' || strlen($payload) > 1800) {
            $payload = $this->publicRoute('demo');
        }

        $result = $this->qrResult($payload, 420);

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    public function download(Request $request, Invitation $invitation): Response
    {
        $invitation->load('template.category');
        $png = $this->renderCardPng($invitation);
        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => $disposition.'; filename="'.$invitation->code.'.png"',
        ]);
    }

    public function admin(): View
    {
        return view('admin', [
            'categories' => Category::orderBy('name')->get(),
            'templates' => CardTemplate::with('category')->latest()->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:80']]);
        Category::firstOrCreate(
            ['slug' => Str::slug($validated['name'])],
            ['name' => $validated['name']]
        );

        return back()->with('status', 'Category saved.');
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'template_image' => ['nullable', 'image', 'max:4096'],
            'motif' => ['required', 'in:arch,crescent,lantern,floral,confetti,diya'],
            'color_one' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_two' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $imageUrl = null;
        if ($request->hasFile('template_image')) {
            $file = $request->file('template_image');
            $filename = time().'-'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/templates'), $filename);
            $imageUrl = '/uploads/templates/'.$filename;
        }

        $slugBase = Str::slug($validated['name']);
        $slug = $slugBase;
        $count = 2;
        while (CardTemplate::where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$count++;
        }

        CardTemplate::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'image_url' => $imageUrl ?: route('card.art', ['slug' => $slug], false),
            'theme' => [
                'title' => $validated['name'],
                'bg' => [$validated['color_one'], $validated['color_two'], $validated['accent']],
                'accent' => $validated['accent'],
                'second' => '#fff8e8',
            ],
            'motif' => $validated['motif'],
        ]);

        return back()->with('status', 'Template saved.');
    }

    public function cardArt(string $slug): Response
    {
        $template = CardTemplate::where('slug', $slug)->first();
        $theme = $template?->theme ?: $this->fallbackTheme($slug);
        $motif = $template?->motif ?: ($theme['motif'] ?? 'arch');
        $svg = $this->renderCardSvg($theme, $motif);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function makeCode(): string
    {
        do {
            $code = 'MF-'.Str::upper(Str::random(6));
        } while (Invitation::where('code', $code)->exists());

        return $code;
    }

    private function publicRoute(string $name, mixed $parameters = []): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');

        return $baseUrl.route($name, $parameters, false);
    }

    private function qrPayload(Invitation $invitation): string
    {
        $time = $this->formatTime($invitation->event_time);
        $date = $invitation->event_date?->format('d M Y');

        return implode("\n", array_filter([
            'MEHFILCARDS INVITE',
            'CODE: '.$invitation->code,
            'OCCASION: '.$invitation->occasion,
            'EVENT: '.$invitation->event_name,
            'GUEST: '.$invitation->guest_name,
            'HOST: '.$invitation->host_name,
            'DATE: '.trim($date.' '.$time),
            'VENUE: '.$invitation->venue,
            $invitation->whatsapp ? 'CONTACT: '.$invitation->whatsapp : null,
            'LINK: '.$this->publicRoute('invite.show', $invitation),
        ]));
    }

    private function extractCode(string $payload): ?string
    {
        if (preg_match('/MF-[A-Z0-9]{4,12}/i', $payload, $matches)) {
            return Str::upper($matches[0]);
        }

        $lastSegment = basename(parse_url($payload, PHP_URL_PATH) ?: '');
        if (preg_match('/^[A-Z0-9-]{4,20}$/i', $lastSegment)) {
            return Str::upper($lastSegment);
        }

        return null;
    }

    private function decodePreviewPayload(string $payload): ?array
    {
        if (!str_starts_with($payload, 'MEHFIL-PREVIEW:')) {
            return null;
        }

        $encoded = substr($payload, strlen('MEHFIL-PREVIEW:'));
        $encoded = strtr($encoded, '-_', '+/');
        $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $json = base64_decode($encoded, true);

        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : null;
    }

    private function defaultGreeting(string $occasion): string
    {
        $map = [
            'eid' => 'Eid Mubarak',
            'ramzan' => 'Ramzan Kareem',
            'ramadan' => 'Ramadan Kareem',
            'dawat' => 'Aapki Dawat Hai',
            'walima' => 'Walima Mubarak',
            'shadi' => 'Shadi Mubarak',
            'wedding' => 'Wedding Invitation',
            'party' => 'You Are Invited',
            'diwali' => 'Happy Diwali',
            'holi' => 'Happy Holi',
            'christmas' => 'Merry Christmas',
            'new year' => 'Happy New Year',
            'birthday' => 'Happy Birthday',
        ];

        return $map[Str::lower($occasion)] ?? $occasion.' Invitation';
    }

    private function qrResult(string $data, int $size)
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        return $builder->build();
    }

    private function qrDataUri(string $data, int $size): string
    {
        return $this->qrResult($data, $size)->getDataUri();
    }

    private function renderCardPng(Invitation $invitation): string
    {
        $width = 1080;
        $height = 1440;
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $template = $invitation->template;
        $theme = $template?->theme ?: $this->fallbackTheme($invitation->occasion);

        if ($template?->image_url && str_starts_with($template->image_url, '/uploads/templates/')) {
            $loaded = $this->loadRaster(public_path(ltrim($template->image_url, '/')));
            if ($loaded) {
                imagecopyresampled($image, $loaded, 0, 0, 0, 0, $width, $height, imagesx($loaded), imagesy($loaded));
                imagedestroy($loaded);
            } else {
                $this->paintGradient($image, $theme);
            }
        } else {
            $this->paintGradient($image, $theme);
            $this->paintMotif($image, $template?->motif ?: 'arch', $theme);
        }

        $accent = $this->rgb($theme['accent'] ?? '#d8b45f');
        $light = $this->rgb($theme['second'] ?? '#fff8e8');
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, 45);
        $accentColor = imagecolorallocate($image, $accent[0], $accent[1], $accent[2]);
        $lightColor = imagecolorallocate($image, $light[0], $light[1], $light[2]);
        $white = imagecolorallocate($image, 255, 255, 255);
        $fontBold = $this->fontPath('georgiab.ttf');
        $fontRegular = $this->fontPath('georgia.ttf');

        $greeting = $invitation->custom_greeting ?: $this->defaultGreeting($invitation->occasion);
        $this->centerText($image, $greeting, 52, (int) ($template->greeting_y ?? 980), $fontBold, $accentColor, $shadow, 900);
        $this->centerText($image, $invitation->guest_name, 64, (int) ($template->name_y ?? 1070), $fontBold, $white, $shadow, 920);
        $this->centerText($image, 'Hosted by '.$invitation->host_name, 30, (int) ($template->host_y ?? 1140), $fontRegular, $lightColor, $shadow, 900);
        $this->centerText($image, $invitation->event_name, 34, 1240, $fontRegular, $lightColor, $shadow, 920);

        $dateLine = trim($invitation->event_date->format('d M Y').'  '.$this->formatTime($invitation->event_time));
        $this->centerText($image, $dateLine, 27, 1294, $fontRegular, $lightColor, $shadow, 900);
        $this->centerText($image, $invitation->venue, 24, 1346, $fontRegular, $lightColor, $shadow, 940);

        $qrImage = imagecreatefromstring($this->qrResult($this->qrPayload($invitation), 560)->getString());
        if ($qrImage) {
            $qrX = (int) ($template->qr_x ?? 72);
            $qrY = (int) ($template->qr_y ?? 72);
            $qrSize = 220;
            imagefilledrectangle($image, $qrX - 12, $qrY - 12, $qrX + $qrSize + 12, $qrY + $qrSize + 12, $white);
            imagecopyresampled($image, $qrImage, $qrX, $qrY, 0, 0, $qrSize, $qrSize, imagesx($qrImage), imagesy($qrImage));
            imagedestroy($qrImage);
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return $png;
    }

    private function paintGradient($image, array $theme): void
    {
        [$start, $middle, $end] = $theme['bg'] ?? ['#071f1a', '#123d34', '#d8b45f'];
        $startRgb = $this->rgb($start);
        $middleRgb = $this->rgb($middle);
        $endRgb = $this->rgb($end);
        $height = imagesy($image);
        $width = imagesx($image);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $from = $ratio < 0.72 ? $startRgb : $middleRgb;
            $to = $ratio < 0.72 ? $middleRgb : $endRgb;
            $local = $ratio < 0.72 ? $ratio / 0.72 : ($ratio - 0.72) / 0.28;
            $color = imagecolorallocate(
                $image,
                (int) ($from[0] + ($to[0] - $from[0]) * $local),
                (int) ($from[1] + ($to[1] - $from[1]) * $local),
                (int) ($from[2] + ($to[2] - $from[2]) * $local)
            );
            imageline($image, 0, $y, $width, $y, $color);
        }
    }

    private function paintMotif($image, string $motif, array $theme): void
    {
        $accent = $this->rgb($theme['accent'] ?? '#d8b45f');
        $second = $this->rgb($theme['second'] ?? '#fff8e8');
        $accentColor = imagecolorallocatealpha($image, $accent[0], $accent[1], $accent[2], 22);
        $lineColor = imagecolorallocatealpha($image, $second[0], $second[1], $second[2], 72);

        imagerectangle($image, 54, 54, 1026, 1386, $lineColor);
        imagerectangle($image, 72, 72, 1008, 1368, $lineColor);

        if ($motif === 'crescent') {
            imagefilledellipse($image, 785, 240, 190, 190, $accentColor);
            imagefilledellipse($image, 825, 210, 180, 180, imagecolorallocatealpha($image, 10, 30, 30, 18));
            return;
        }

        if ($motif === 'lantern') {
            imagerectangle($image, 486, 170, 594, 355, $accentColor);
            imageellipse($image, 540, 258, 118, 190, $lineColor);
            imageline($image, 540, 120, 540, 170, $lineColor);
            return;
        }

        if ($motif === 'floral') {
            for ($i = 0; $i < 9; $i++) {
                imagefilledellipse($image, 185 + ($i * 86), 248 + (($i % 2) * 36), 54, 54, $accentColor);
                imageellipse($image, 185 + ($i * 86), 248 + (($i % 2) * 36), 72, 72, $lineColor);
            }
            return;
        }

        if ($motif === 'diya') {
            imagefilledarc($image, 540, 315, 250, 115, 0, 180, $accentColor, IMG_ARC_PIE);
            imagefilledellipse($image, 540, 230, 58, 96, imagecolorallocatealpha($image, 255, 245, 180, 18));
            return;
        }

        if ($motif === 'confetti') {
            for ($i = 0; $i < 55; $i++) {
                $x = 120 + (($i * 137) % 840);
                $y = 120 + (($i * 83) % 410);
                imagefilledrectangle($image, $x, $y, $x + 22, $y + 8, $accentColor);
            }
            return;
        }

        imagearc($image, 540, 520, 560, 620, 180, 360, $lineColor);
        imagearc($image, 540, 520, 440, 500, 180, 360, $lineColor);
    }

    private function centerText($image, string $text, int $size, int $y, string $font, int $color, int $shadow, int $maxWidth): void
    {
        $lines = $this->wrapText($text, $size, $font, $maxWidth);
        $lineHeight = (int) round($size * 1.25);
        $startY = $y - (int) ((count($lines) - 1) * $lineHeight / 2);

        foreach ($lines as $index => $line) {
            $box = imagettfbbox($size, 0, $font, $line);
            $textWidth = abs($box[2] - $box[0]);
            $x = (int) ((imagesx($image) - $textWidth) / 2);
            $lineY = $startY + ($index * $lineHeight);
            imagettftext($image, $size, 0, $x + 3, $lineY + 3, $shadow, $font, $line);
            imagettftext($image, $size, 0, $x, $lineY, $color, $font, $line);
        }
    }

    private function wrapText(string $text, int $size, string $font, int $maxWidth): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = trim($line.' '.$word);
            $box = imagettfbbox($size, 0, $font, $candidate);
            $width = abs($box[2] - $box[0]);
            if ($line !== '' && $width > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines ?: [$text];
    }

    private function fontPath(string $name): string
    {
        $paths = [
            'C:\\Windows\\Fonts\\'.$name,
            'C:\\Windows\\Fonts\\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return __DIR__;
    }

    private function loadRaster(string $path)
    {
        if (!is_file($path)) {
            return false;
        }

        $type = mime_content_type($path);
        return match ($type) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function formatTime(?string $time): string
    {
        if (!$time) {
            return '';
        }

        return Carbon::createFromFormat(strlen($time) === 5 ? 'H:i' : 'H:i:s', $time)->format('h:i A');
    }

    private function fallbackTheme(string $key): array
    {
        $themes = $this->seedTemplateData();
        foreach ($themes as $template) {
            if (str_contains(Str::lower($key), Str::lower($template['category'])) || str_contains($template['slug'], Str::slug($key))) {
                return $template['theme'] + ['motif' => $template['motif']];
            }
        }

        return [
            'title' => 'You Are Invited',
            'bg' => ['#10131f', '#3153a4', '#f7c45f'],
            'accent' => '#f7c45f',
            'second' => '#eaf0ff',
            'motif' => 'arch',
        ];
    }

    private function renderCardSvg(array $theme, string $motif): string
    {
        $bg = $theme['bg'] ?? ['#071f1a', '#123d34', '#d8b45f'];
        $accent = $theme['accent'] ?? '#d8b45f';
        $second = $theme['second'] ?? '#fff8e8';
        $title = e($theme['title'] ?? 'You Are Invited');

        $motifSvg = match ($motif) {
            'crescent' => '<circle cx="760" cy="245" r="94" fill="'.$accent.'" opacity=".78"/><circle cx="798" cy="212" r="90" fill="url(#bg)"/>',
            'lantern' => '<path d="M480 150h120l-24 52h-72z" fill="'.$accent.'" opacity=".75"/><rect x="500" y="198" width="80" height="150" rx="32" fill="none" stroke="'.$accent.'" stroke-width="7"/>',
            'floral' => '<g opacity=".65"><circle cx="230" cy="250" r="46" fill="'.$accent.'"/><circle cx="850" cy="250" r="46" fill="'.$accent.'"/><path d="M250 290c190 75 390 75 580 0" fill="none" stroke="'.$second.'" stroke-width="5"/></g>',
            'diya' => '<path d="M390 300c90 80 210 80 300 0 0 75-300 75-300 0z" fill="'.$accent.'" opacity=".74"/><path d="M540 180c48 58 38 116 0 150-38-34-48-92 0-150z" fill="'.$second.'" opacity=".75"/>',
            'confetti' => '<g opacity=".72"><rect x="185" y="180" width="58" height="16" fill="'.$accent.'" transform="rotate(18 185 180)"/><rect x="798" y="245" width="64" height="16" fill="'.$second.'" transform="rotate(-22 798 245)"/><circle cx="350" cy="255" r="12" fill="'.$accent.'"/><circle cx="705" cy="170" r="10" fill="'.$second.'"/></g>',
            default => '<path d="M270 585c0-195 135-350 270-350s270 155 270 350" fill="none" stroke="'.$second.'" stroke-width="8" opacity=".45"/><path d="M350 585c0-145 95-265 190-265s190 120 190 265" fill="none" stroke="'.$accent.'" stroke-width="5" opacity=".7"/>',
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1080" height="1440" viewBox="0 0 1080 1440">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="{$bg[0]}"/>
      <stop offset="68%" stop-color="{$bg[1]}"/>
      <stop offset="100%" stop-color="{$bg[2]}"/>
    </linearGradient>
  </defs>
  <rect width="1080" height="1440" fill="url(#bg)"/>
  <rect x="54" y="54" width="972" height="1332" fill="none" stroke="{$second}" stroke-opacity=".34" stroke-width="3"/>
  <rect x="72" y="72" width="936" height="1296" fill="none" stroke="{$accent}" stroke-opacity=".36" stroke-width="2"/>
  {$motifSvg}
  <text x="540" y="805" fill="{$second}" font-family="Georgia, serif" font-size="48" text-anchor="middle" opacity=".82">{$title}</text>
  <text x="540" y="890" fill="{$accent}" font-family="Georgia, serif" font-size="24" text-anchor="middle" letter-spacing="4">MEHFILCARDS</text>
</svg>
SVG;
    }

    public function seedTemplateData(): array
    {
        return [
            ['category' => 'Eid', 'name' => 'Eid Royal Crescent', 'slug' => 'eid-royal-crescent', 'motif' => 'crescent', 'theme' => ['title' => 'Eid Mubarak', 'bg' => ['#071f1a', '#123d34', '#d8b45f'], 'accent' => '#d8b45f', 'second' => '#f6ead0']],
            ['category' => 'Ramzan', 'name' => 'Ramzan Noor', 'slug' => 'ramzan-noor', 'motif' => 'lantern', 'theme' => ['title' => 'Ramzan Kareem', 'bg' => ['#0d1832', '#2a557a', '#e4c56e'], 'accent' => '#e4c56e', 'second' => '#eef4ff']],
            ['category' => 'Dawat', 'name' => 'Dawat Mehfil', 'slug' => 'dawat-mehfil', 'motif' => 'arch', 'theme' => ['title' => 'Dawat Invitation', 'bg' => ['#2a1231', '#7c3158', '#f0b86f'], 'accent' => '#f0b86f', 'second' => '#fff0dc']],
            ['category' => 'Walima', 'name' => 'Walima Pearl', 'slug' => 'walima-pearl', 'motif' => 'floral', 'theme' => ['title' => 'Walima Mubarak', 'bg' => ['#191827', '#6d5b8d', '#d9c7a3'], 'accent' => '#d9c7a3', 'second' => '#fff9ef']],
            ['category' => 'Shadi', 'name' => 'Shadi Gulab', 'slug' => 'shadi-gulab', 'motif' => 'floral', 'theme' => ['title' => 'Shadi Mubarak', 'bg' => ['#331119', '#9b2847', '#f6b7a7'], 'accent' => '#f6b7a7', 'second' => '#fff2ef']],
            ['category' => 'Wedding', 'name' => 'Wedding Classic', 'slug' => 'wedding-classic', 'motif' => 'floral', 'theme' => ['title' => 'Wedding Celebration', 'bg' => ['#27133a', '#8a4b78', '#f0c08b'], 'accent' => '#f0c08b', 'second' => '#fff2ef']],
            ['category' => 'Party', 'name' => 'Party Luxe Night', 'slug' => 'party-luxe-night', 'motif' => 'confetti', 'theme' => ['title' => 'Party Night', 'bg' => ['#10131f', '#3153a4', '#f7c45f'], 'accent' => '#f7c45f', 'second' => '#eaf0ff']],
            ['category' => 'Festivals', 'name' => 'Festival Rang', 'slug' => 'festival-rang', 'motif' => 'confetti', 'theme' => ['title' => 'Festival Greetings', 'bg' => ['#1d1838', '#d05a45', '#ffd36d'], 'accent' => '#ffd36d', 'second' => '#fff5d4']],
            ['category' => 'Holi', 'name' => 'Holi Rang', 'slug' => 'holi-rang', 'motif' => 'confetti', 'theme' => ['title' => 'Happy Holi', 'bg' => ['#20305a', '#d4528f', '#ffd86a'], 'accent' => '#ffd86a', 'second' => '#fff5ff']],
            ['category' => 'Diwali', 'name' => 'Diwali Deep Glow', 'slug' => 'diwali-deep-glow', 'motif' => 'diya', 'theme' => ['title' => 'Happy Diwali', 'bg' => ['#231235', '#a9442a', '#ffd36d'], 'accent' => '#ffd36d', 'second' => '#fff1ca']],
            ['category' => 'Christmas', 'name' => 'Christmas Warm Lights', 'slug' => 'christmas-warm-lights', 'motif' => 'confetti', 'theme' => ['title' => 'Merry Christmas', 'bg' => ['#0d1d19', '#8f2634', '#f2d492'], 'accent' => '#f2d492', 'second' => '#fff6dc']],
            ['category' => 'New Year', 'name' => 'New Year Luxe', 'slug' => 'new-year-luxe', 'motif' => 'confetti', 'theme' => ['title' => 'Happy New Year', 'bg' => ['#091221', '#254b83', '#f5c45d'], 'accent' => '#f5c45d', 'second' => '#edf4ff']],
            ['category' => 'Mehandi', 'name' => 'Mehandi Green Mehfil', 'slug' => 'mehandi-green-mehfil', 'motif' => 'floral', 'theme' => ['title' => 'Mehandi Mubarak', 'bg' => ['#102117', '#28663c', '#e2b95f'], 'accent' => '#e2b95f', 'second' => '#eff8dc']],
            ['category' => 'Engagement', 'name' => 'Engagement Rose', 'slug' => 'engagement-rose', 'motif' => 'floral', 'theme' => ['title' => 'Engagement Mubarak', 'bg' => ['#241126', '#9c3f62', '#f6c0a7'], 'accent' => '#f6c0a7', 'second' => '#fff0eb']],
            ['category' => 'Anniversary', 'name' => 'Anniversary Glow', 'slug' => 'anniversary-glow', 'motif' => 'arch', 'theme' => ['title' => 'Happy Anniversary', 'bg' => ['#17152a', '#764c8f', '#f1c982'], 'accent' => '#f1c982', 'second' => '#fff3dc']],
            ['category' => 'Birthday', 'name' => 'Birthday Bloom', 'slug' => 'birthday-bloom', 'motif' => 'confetti', 'theme' => ['title' => 'Happy Birthday', 'bg' => ['#1b1a33', '#b84885', '#ffcb70'], 'accent' => '#ffcb70', 'second' => '#fff1f8']],
            ['category' => 'Corporate', 'name' => 'Corporate Gala', 'slug' => 'corporate-gala', 'motif' => 'arch', 'theme' => ['title' => 'Gala Invitation', 'bg' => ['#08111f', '#1f4e5f', '#c4a35a'], 'accent' => '#c4a35a', 'second' => '#e8f5f8']],
            ['category' => 'Aqeeqah', 'name' => 'Aqeeqah Soft Noor', 'slug' => 'aqeeqah-soft-noor', 'motif' => 'lantern', 'theme' => ['title' => 'Aqeeqah Mubarak', 'bg' => ['#172032', '#478d8f', '#f1d3a2'], 'accent' => '#f1d3a2', 'second' => '#f2fffb']],
        ];
    }
}
