<?php

namespace App\Models;

use App\Enum\User\UserRole;
use App\Enum\User\UserStatus;
use Laravel\Sanctum\HasApiTokens;
use App\Mail\SendPasswordResetCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\SendEmailVerificationCode;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'status',
        'verify_otp',
        'email_otp_expires_at',
        'password_reset_code',
        'password_reset_expires_at',
        'facebook_id',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verify_otp',
        'password_reset_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_otp_expires_at' => 'datetime',
            'password_reset_expires_at' => 'datetime',
            'password' => 'hashed',
            // 'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    // Accessors
    public function getAvatarUrlAttribute(): ?string
    {
        return Cache::remember("user_avatar_{$this->id}", 3600, function () {
            return $this->avatar ? Storage::disk('s3')->url($this->avatar) : null;
        });
    }

    // Relationships
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    // Methods
    public function generateAndSendVerificationCode(): void
    {
        $this->update([
            'verify_otp' => rand(100000, 999999),
            'email_otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($this->email)->queue(new SendEmailVerificationCode($this));
    }

    public function sendPasswordResetCode(): void
    {
        $this->update([
            'password_reset_code' => rand(100000, 999999),
            'password_reset_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($this->email)->queue(new SendPasswordResetCode($this));
    }



    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function canLogin(): bool
    {
        return $this->status->canLogin();
    }

    public function canPlaceOrder(): bool
    {
        return $this->status->canPlaceOrder();
    }

    public function canLeaveReview(): bool
    {
        return $this->status->canLeaveReview();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission) || $this->isSuperAdmin();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::ACTIVE);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', [UserRole::ADMIN, UserRole::SUPER_ADMIN, UserRole::MANAGER]);
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {

        return $this->hasPermission('access_admin_panel') || $this->isSuperAdmin();
    }
}
