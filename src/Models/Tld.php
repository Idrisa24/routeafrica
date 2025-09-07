<?php

namespace Saidtech\Routereseller\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tld extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registrar',
        'register_price',
        'renewal_price',
        'transfer_price',
        'min_years',
        'max_years',
        'is_active',
        'supports_privacy',
        'supports_transfer',
        'requirements',
        'sort_order',
    ];

    protected $casts = [
        'register_price' => 'decimal:2',
        'renewal_price' => 'decimal:2',
        'transfer_price' => 'decimal:2',
        'min_years' => 'integer',
        'max_years' => 'integer',
        'is_active' => 'boolean',
        'supports_privacy' => 'boolean',
        'supports_transfer' => 'boolean',
        'requirements' => 'array',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function getPrice($type = 'register'): float
    {
        return match ($type) {
            'register' => $this->register_price,
            'renewal' => $this->renewal_price,
            'transfer' => $this->transfer_price,
            default => $this->register_price,
        };
    }

    public function getRequirement($key, $default = null)
    {
        return data_get($this->requirements, $key, $default);
    }

    public function hasRequirement($key): bool
    {
        return !empty($this->getRequirement($key));
    }
}
