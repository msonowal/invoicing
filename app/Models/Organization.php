<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Organization extends JetstreamTeam
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'slug',
        'custom_domain',
        'phone',
        'emails',
        'primary_location_id',
        'currency',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teams';

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'emails' => \App\Casts\EmailCollectionCast::class,
            'currency' => \App\Currency::class,
        ];
    }

    public function locations(): MorphMany
    {
        return $this->morphMany(Location::class, 'locatable');
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'organization_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'organization_id');
    }

    public function getUrlAttribute(): string
    {
        if ($this->custom_domain) {
            return "https://{$this->custom_domain}";
        }

        if ($this->slug) {
            return "https://{$this->slug}.clarity-invoicing.com";
        }

        return "https://clarity-invoicing.com/organizations/{$this->id}";
    }
}
