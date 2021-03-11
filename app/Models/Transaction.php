<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'food_id',
        'quantity', 'total',
        'status', 'payment_url',
    ];

    /**
     * The attributes that should be cast to native types.
     * casting created_at and updated_at to UnixTimestamp
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];

    /**
     * Get the food associated with the transaction
     * since a transaction has only one food, then we can use one-to-one relationship
     *
     * @see https://laravel.com/docs/8.x/eloquent-relationships#one-to-one
     *
     * return App\Model\Food
     */
    public function food()
    {
        return $this->hasOne(Food::class, 'id', 'food_id');
    }

    /**
     * Get the user associated with the transaction
     * since a transaction has only one user, then we can use one-to-one relationship
     *
     * @see https://laravel.com/docs/8.x/eloquent-relationships#one-to-one
     *
     * return App\Model\User
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
