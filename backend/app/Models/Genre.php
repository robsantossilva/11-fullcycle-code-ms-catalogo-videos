<?php

namespace App\Models;

use App\ModelFilters\GenreFilter;
use App\Models\Traits\SerializeDateToISO8601;
use App\Models\Traits\Uuid;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use SoftDeletes, Uuid, SerializeDateToISO8601, HasBelongsToManyEvents, Filterable;

    const RELATED_TABLES = [
        'categories'=>'categories_id'
    ];

    protected   $fillable = ['name','description','is_active'];
    protected   $dates = ['deleted_at'];
    public      $incrementing = false;
    protected   $keyType = 'string';
    protected   $casts = ['id'=>'string','is_active'=>'boolean'];
    protected   $observables = [
        'belongsToManyAttached'
    ];

    public function categories() : BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function modelFilter()
    {
        return $this->provideFilter(GenreFilter::class);
    }

}
