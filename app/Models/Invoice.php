<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'type',
        'ulid',
        'company_location_id',
        'customer_location_id',
        'invoice_number',
        'status',
        'issued_at',
        'due_at',
        'subtotal',
        'tax',
        'total',
        'company_id',
        'currency',
        'subject',
        'notes',
        'adjustment',
        'tds',
        'tcs',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'currency' => \App\Currency::class,
        ];
    }

    public function getTdsAttribute($value): ?float
    {
        if ($value === null) {
            return null;
        }

        if ($value === 0) {
            return 0.0;
        }

        return round($value / 100.0, 2);
    }

    public function setTdsAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['tds'] = null;

            return;
        }

        $this->attributes['tds'] = (int) round((float) $value * 100);
    }

    public function getTcsAttribute($value): ?float
    {
        if ($value === null) {
            return null;
        }

        if ($value === 0) {
            return 0.0;
        }

        return round($value / 100.0, 2);
    }

    public function setTcsAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['tcs'] = null;

            return;
        }

        $this->attributes['tcs'] = (int) round((float) $value * 100);
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function companyLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'company_location_id');
    }

    public function customerLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'customer_location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function isInvoice(): bool
    {
        return $this->type === 'invoice';
    }

    public function isEstimate(): bool
    {
        return $this->type === 'estimate';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
    }
}
