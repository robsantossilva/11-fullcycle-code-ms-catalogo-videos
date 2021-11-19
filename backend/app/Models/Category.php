<?php

namespace App\Models;

use App\ModelFilters\CategoryFilter;
use App\Models\Traits\SerializeDateToISO8601;
use App\Models\Traits\Uuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes, Uuid, SerializeDateToISO8601, Filterable;
    protected   $fillable = ['name','description','is_active'];
    protected   $dates = ['deleted_at'];
    public      $incrementing = false;
    protected   $keyType = 'string';
    protected   $casts = [
        'id'=>'string',
        'is_active'=>'boolean'
    ];

    public function genres() : BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function modelFilter() {
        return $this->provideFilter(CategoryFilter::class);
    }
}
