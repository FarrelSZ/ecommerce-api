<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\Product\Review;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Authenticatable implements Wallet
{
    use HasFactory, Notifiable, HasApiTokens, HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'phone',
        'store_name',
        'gender',
        'birth_date',
        'photo',
        'otp_register',
        'email_verified_at',
        'social_media_id',
        'social_media_provider',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'float',
        ];
    }

    public function getApiResponseAttribute()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'photo_url' => $this->photo_url,
            'username' => $this->username,
            'phone' => $this->phone,
            'store_name' => $this->store_name,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'balance' => (float) $this->balance,
        ];
    }

    public function getApiResponseAsBuyerAttribute()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'photo_url' => $this->photo_url,
            'username' => $this->username,
            'phone' => $this->phone,
        ];
    }

    public function getApiResponseAsSellerAttribute()
    {
        $productIds = $this->products()->pluck('id');

        return [
            'username' => $this->username,
            'store_name' => $this->store_name,
            'photo_url' => $this->photo_url,
            'product_count' => $this->products()->count(),
            'rating_count' => Review::whereIn('product_id', $productIds)->count(),
            'join_date' => $this->created_at->diffForHumans(),
            'send_from' => optional($this->addresses()->where('is_default', true)->first())->getApiResponseAttribute()
        ];
    }

    public function getPhotoUrlAttribute()
    {
        if (!is_null($this->photo)) {
            return null;
        }
        return asset('storage/' . $this->photo);
    }

    public function addresses()
    {
        return $this->hasMany(\App\Models\Address\Address::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }
    public function ordersAsSeller()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }
    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'seller_id');
    }
}
