<?php

namespace Database\Seeders;

use App\Models\CardTemplate;
use App\Models\Category;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mehfilcards.com'],
            [
                'name' => 'MehfilCards Admin',
                'password' => 'password123',
            ]
        );

        $templates = $this->templates();

        foreach (collect($templates)->pluck('category')->unique() as $categoryName) {
            Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );
        }

        foreach ($templates as $template) {
            $category = Category::where('slug', Str::slug($template['category']))->firstOrFail();

            CardTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $template['name'],
                    'image_url' => '/card-art/'.$template['slug'].'.svg',
                    'theme' => $template['theme'],
                    'motif' => $template['motif'],
                    'greeting_y' => $template['greeting_y'] ?? 980,
                    'name_y' => $template['name_y'] ?? 1070,
                    'host_y' => $template['host_y'] ?? 1140,
                    'qr_x' => $template['qr_x'] ?? 72,
                    'qr_y' => $template['qr_y'] ?? 72,
                    'active' => true,
                ]
            );
        }

        $demoTemplate = CardTemplate::where('slug', 'eid-royal-crescent')->first();
        Invitation::updateOrCreate(
            ['code' => 'MF-DEMO26'],
            [
                'card_template_id' => $demoTemplate?->id,
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
    }

    private function templates(): array
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
