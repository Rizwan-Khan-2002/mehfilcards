# MehfilCards

MehfilCards is a Laravel-based digital invitation platform for creating festival greetings, wedding invitations, party cards, and event QR passes from one clean web dashboard.

It was built for real client use: visitors can create cards, admins can manage categories and templates, generated invitations include QR verification, and clients can contact the service owner directly from the public page.

## Highlights

- Multi-occasion card creator for Eid, Ramzan, Dawat, Walima, Shadi, Wedding, Party, Diwali, Holi, Christmas, New Year, Birthday, Corporate, Aqeeqah, and custom occasions.
- Manual occasion input so users can type any event name, such as Shop Opening, Nikah, House Party, Reception, or Naming Ceremony.
- Live invitation preview with selectable templates.
- Server-generated invitation PNG download.
- Real QR generation with invite code, event details, contact data, and public invite link.
- QR scanner and manual verification screen.
- Admin panel for adding categories and uploading custom template artwork.
- Public contact section with WhatsApp, email, and payment links.
- Working newsletter subscription form saved to the database.
- Day and night mode toggle with saved browser preference.
- Mobile testing helper for local network QR scans.

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL / MariaDB
- Bootstrap 5
- jQuery
- Endroid QR Code
- HTML Canvas preview
- GD image rendering for PNG cards

## Core Pages

| Page | Purpose |
| --- | --- |
| `/` | Public card creator, contact section, newsletter form |
| `/login` | Admin login |
| `/register` | Admin registration |
| `/admin` | Categories and template management |
| `/scanner` | QR verification and manual invite lookup |
| `/payments` | Payment/contact information |
| `/demo` | Demo invitation |

## Local Setup

Clone the project and install dependencies:

```bash
composer install
npm install
```

Create the environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mehfilcards_laravel
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed the default categories/templates:

```bash
php artisan migrate --seed
```

Start the local server:

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

Open:

```text
http://127.0.0.1:8001
```

## Mobile QR Testing

For phone QR scans on the same Wi-Fi network, run:

```text
start-mobile-server.bat
```

Then open the network URL shown in the terminal, for example:

```text
http://192.168.1.7:8002
```

Keep the server window open while testing QR scans. If Windows Firewall asks for permission, allow PHP on the private network.

## Admin Workflow

1. Register or log in as an admin.
2. Add new categories from the admin panel.
3. Upload custom artwork or create theme-based templates.
4. Return to the public creator and choose the new category/template.
5. Generate the invitation and download the PNG.
6. Scan the QR from `/scanner` to verify invite data.

## QR Verification

Every generated invitation includes a QR payload with:

- Invite code
- Occasion
- Event name
- Guest name
- Host name
- Event date and venue
- Contact number
- Public invitation link

This means the QR remains useful even when a mobile browser cannot reach a local development server.

## Useful Commands

```bash
php artisan test
php artisan migrate:fresh --seed
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Project Structure

```text
app/Http/Controllers/InvitationController.php  Main invitation, QR, admin, payment, and subscribe logic
resources/views/home.blade.php                 Public creator and contact page
resources/views/admin.blade.php                Admin dashboard
resources/views/scanner.blade.php              QR verification UI
resources/views/layouts/app.blade.php          Shared layout, navbar, footer, theme toggle
public/css/mehfilcards.css                     Full UI styling and day/night mode
public/js/mehfilcards.js                       Canvas preview and live QR preview
database/migrations/                           Database schema
```

## Contact

- WhatsApp: `+91 8009030734`
- Email: `rizwan.creativeswork@gmail.com`

## License

This project is open for portfolio and client demonstration use. Add your preferred license before distributing it commercially.
