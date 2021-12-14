<?php

namespace App\Models;

use App\Models\Traits\SerializeDateToISO8601;
use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid, UploadFiles, SerializeDateToISO8601;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    const THUMB_FILE_MAX_SIZE = 1024 * 5; //5MB
    const BANNER_FILE_MAX_SIZE = 1024 * 10; //10MB
    const TRAILER_FILE_MAX_SIZE = 1024 * 1024 * 1; //1GB
    const VIDEO_FILE_MAX_SIZE = 1024 * 1024 * 50; //50GB

    const RELATED_TABLES = [
        'categories' => 'categories_id',
        'genres' => 'genres_id',
        'castMembers' => 'cast_members_id'
    ];

    protected   $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file',
        'thumb_file',
        'banner_file',
        'trailer_file'
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

    public static $fileFields = [
        'video_file',
        'thumb_file',
        'banner_file',
        'trailer_file'
    ];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            /** @var Video $obj */
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            //$obj->load(array_keys(self::relatedTables()))->refresh();
            $obj->uploadFiles($files);

            \DB::commit();
            return $obj;
        } catch (\Exception $e) {
            if (isset($obj)) {
                $obj->deleteFiles($files);
            }
            \DB::rollBack();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            //$this->load(array_keys(self::relatedTables()))->refresh();
            if($saved){
                $this->uploadFiles($files);
            }
            \DB::commit();
            if($saved && count($files)){
                $this->deleteOldFiles();
            }
            return $saved;

        } catch (\Exception $e) {

            $this->deleteFiles($files);

            \DB::rollBack();
            throw $e;
        }
    }

    protected static function relatedTables() : array
    {
        return self::RELATED_TABLES;
    }

    public static function handleRelations(Video $obj, array $attributes) {
        foreach(self::relatedTables() as $table => $field){
            if(isset($attributes[$field])){
                $obj->$table()->sync($attributes[$field]);
            }
        }
    }

    //php artisan make:migration create_genre_video_table
    //php artisan make:migration create_category_video_table

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    public function castMembers(): BelongsToMany {
        return $this->belongsToMany(CastMember::class)->withTrashed();
    }

    protected function uploadDir()
    {
       return $this->id;
    }

    public function getThumbFileUrlAttribute()
    {
        return $this->thumb_file ? $this->getFileUrl($this->thumb_file) : null;
    }

    public function getBannerFileUrlAttribute($value=null)
    {
        return $this->banner_file ? $this->getFileUrl($this->banner_file) : null;
    }

    public function getTrailerFileUrlAttribute($value=null)
    {
        return $this->trailer_file ? $this->getFileUrl($this->trailer_file) : null;
    }

    public function getVideoFileUrlAttribute($value=null)
    {
        return $this->video_file ? $this->getFileUrl($this->video_file) : null;
    }
}
