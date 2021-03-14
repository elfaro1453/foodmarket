<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\Food.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $ingredients
 * @property int|null $price
 * @property float|null $rate
 * @property string|null $types
 * @property string $picture_path
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $created_at
 * @property int|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Food newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Food newQuery()
 * @method static \Illuminate\Database\Query\Builder|Food onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Food query()
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereIngredients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food wherePicturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Food whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Food withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Food withoutTrashed()
 * @mixin \Eloquent
 */
class Food extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description',
        'ingredients', 'price',
        'rate', 'types',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'picture_path',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'food_picture_url',
    ];

    /**
     * The accessors to get full URL of picture_path.
     *
     * @see https://laravel.com/docs/8.x/eloquent-mutators#accessors-and-mutators
     * @return string baseUrl + picture_path
     */
    public function getFoodPictureUrlAttribute(): ?string
    {
        if (isset($this->attributes['picture_path'])) {
            return Storage::disk('public')->url($this->attributes['picture_path']);
        }

        return null;
    }
}
