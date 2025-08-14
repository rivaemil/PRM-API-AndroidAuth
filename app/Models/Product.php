<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'status',
        'user_id',
    ];

    public function images():MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
