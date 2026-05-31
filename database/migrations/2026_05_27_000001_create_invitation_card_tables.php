<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('card_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image_url')->nullable();
            $table->json('theme')->nullable();
            $table->string('motif')->default('arch');
            $table->unsignedSmallInteger('greeting_y')->default(980);
            $table->unsignedSmallInteger('name_y')->default(1070);
            $table->unsignedSmallInteger('host_y')->default(1140);
            $table->unsignedSmallInteger('qr_x')->default(72);
            $table->unsignedSmallInteger('qr_y')->default(72);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('guest_name');
            $table->string('host_name');
            $table->string('event_name');
            $table->string('occasion');
            $table->string('custom_greeting')->nullable();
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->string('venue');
            $table->string('whatsapp')->nullable();
            $table->text('message')->nullable();
            $table->string('language_mode')->default('english');
            $table->string('rsvp_status')->nullable();
            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invitation_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scanned_payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_scans');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('card_templates');
        Schema::dropIfExists('categories');
    }
};
