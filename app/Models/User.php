<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\User\UserStatus;
use Laravel\Sanctum\HasApiTokens;
use App\Mail\SendPasswordResetCode;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmailVerificationCode;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'is_admin',
        'is_active',
        'verify_otp',
        'email_otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'status' => UserStatus::class,
        ];
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


    public function generateAndSendVerificationCode()
    {

        $this->verify_otp = rand(100000, 999999);
        $this->email_otp_expires_at = now()->addMinutes(10);
        $this->save();


        Mail::to($this->email)->queue(new SendEmailVerificationCode($this));
    }

    public function sendPasswordResetCode()
    {
        $this->password_reset_code = rand(100000, 999999);
        $this->password_reset_expires_at = now()->addMinutes(10);
        $this->save();

        Mail::to($this->email)->queue(new SendPasswordResetCode($this));
    }
}
