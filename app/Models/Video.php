<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected   $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration'
    ];
    
    protected   $dates = ['deleted_at'];
    
    public      $incrementing = false;
    
    protected   $keyType = 'string';
    
    protected   $casts = [
        'id' => 'string',
        'title' => 'string',
        'description' => 'string',
        'year_launched' => 'integer',
        'opened' => 'boolean',
        'rating' => 'string',
        'duration' => 'integer'
    ];

    //php artisan make:migration create_genre_video_table
    //php artisan make:migration create_category_video_table

    public function categories() : BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function genres() : BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }
}
