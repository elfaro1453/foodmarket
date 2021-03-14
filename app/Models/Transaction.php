<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Transaction.
 *
 * @property int $id
 * @property int $user_id
 * @property int $food_id
 * @property int $quantity
 * @property int $total
 * @property string $status
 * @property string $payment_url
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property-read \App\Models\Food|null $food
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Query\Builder|Transaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePaymentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Transaction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Transaction withoutTrashed()
 * @mixin \Eloquent
 */
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
     * casting created_at and updated_at to UnixTimestamp.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];

    /**
     * Get the food associated with the transaction
     * since a transaction has only one food, then we can use one-to-one relationship.
     *
     * @see https://laravel.com/docs/8.x/eloquent-relationships#one-to-one
     *
     * @return HasOne
     */
    public function food(): HasOne
    {
        return $this->hasOne(Food::class, 'id', 'food_id');
    }

    /**
     * Get the user associated with the transaction
     * since a transaction has only one user, then we can use one-to-one relationship.
     *
     * @see https://laravel.com/docs/8.x/eloquent-relationships#one-to-one
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
