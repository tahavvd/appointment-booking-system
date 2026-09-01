<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'photo',
        'base_price',
        'duration_minutes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * Resolve a usable photo URL whether `photo` is a full external
     * URL (used for seeded placeholders) or a local storage path
     * (used once real uploads exist).
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(
            fn() => str_starts_with($this->photo, 'http')
                ? $this->photo
                : asset('storage/' . $this->photo)
        );
    }

    /**
     * Get the addons for the service.
     */
    public function addons(): HasMany
    {
        return $this->hasMany(ServiceAddon::class);
    }

    /**
     * Get the appointments for the service.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
