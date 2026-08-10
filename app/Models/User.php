<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'phone_country', 'accepts_marketing', 'preferred_locale', 'is_blocked'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'accepts_marketing_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date',
            'accepts_marketing' => 'boolean',
            'is_blocked' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Without this contract every authenticated customer can reach /admin
     * whenever APP_ENV is not local. See §4.3.
     *
     * Owner-only for launch: only `super_admin` is admitted. `staff` is
     * deliberately dropped because Filament Shield is not yet wired — roles
     * carry no per-resource permissions, so a `staff` user would get full CRUD
     * over Orders, Customer PII and Settings (including the OAuth secret).
     * Re-add 'staff' here once Shield resource permissions are generated and
     * scoped (see §4.3): change to hasAnyRole(['super_admin', 'staff']).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && ! $this->is_blocked
            && $this->hasRole('super_admin');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }
}
