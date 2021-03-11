<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
        'picture_path',
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
     * The accessors to get full URL of picture_path
     *
     * @see https://laravel.com/docs/8.x/eloquent-mutators#accessors-and-mutators
     * @param  $value picture_path column
     * @return string baseUrl + picture_path
     */
    public function getPicturePathAttribute($value)
    {
        return url('') . Storage::url($this->attributes['picture_path']);
    }
}
