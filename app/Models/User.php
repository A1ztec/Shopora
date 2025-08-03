<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; {
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
                'is_active' => 'boolean',
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
    }
}
