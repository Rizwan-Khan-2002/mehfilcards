<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_template_id',
        'code',
        'guest_name',
        'host_name',
        'event_name',
        'occasion',
        'custom_greeting',
        'event_date',
        'event_time',
        'venue',
        'whatsapp',
        'message',
        'language_mode',
        'rsvp_status',
        'scan_count',
        'last_scanned_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'last_scanned_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CardTemplate::class, 'card_template_id');
    }
}
