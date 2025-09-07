<?php

namespace Saidtech\Routereseller\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tld_id',
        'name',
        'full_domain',
        'status',
        'registration_date',
        'expiry_date',
        'next_due_date',
        'amount',
        'billing_cycle',
        'nameservers',
        'dns_records',
        'privacy_protection',
        'auto_renew',
        'registrar_data',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'next_due_date' => 'date',
        'amount' => 'decimal:2',
        'nameservers' => 'array',
        'dns_records' => 'array',
        'privacy_protection' => 'boolean',
        'auto_renew' => 'boolean',
        'registrar_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tld()
    {
        return $this->belongsTo(Tld::class);
    }

    public function invoices()
    {
        // return $this->morphMany(InvoiceItem::class, 'relatable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function getRegistrarData($key, $default = null)
    {
        return data_get($this->registrar_data, $key, $default);
    }

    public function setRegistrarData($key, $value): void
    {
        $data = $this->registrar_data ?? [];
        data_set($data, $key, $value);
        $this->registrar_data = $data;
    }

    public function getNameserver($index): ?string
    {
        return $this->nameservers[$index] ?? null;
    }

    public function setNameserver($index, $value): void
    {
        $nameservers = $this->nameservers ?? [];
        $nameservers[$index] = $value;
        $this->nameservers = $nameservers;
    }
}
