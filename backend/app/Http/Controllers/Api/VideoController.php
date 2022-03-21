<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
    private $rules;
    private $loadGenreCategories = false;

    public function __construct()
    {
    }

    protected function relatedTables(): array
    {
        return Video::RELATED_TABLES;
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rules()
    {

        $categoriesId = $this->request->get('categories_id');
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];

        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y|min:1',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer|min:1',
            'categories_id' => [
                'required',
                'array',
                'exists:categories,id,deleted_at,NULL',
                //new CategoryGenreLinked($this->request)
            ],
            'genres_id' => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL',
                //new CategoryGenreLinked($this->request)
                new GenresHasCategoriesRule($categoriesId)
            ],
            'cast_members_id' => [
                'required',
                'array',
                'exists:cast_members,id,deleted_at,NULL',
            ],
            'thumb_file' => 'image|max:' . Video::THUMB_FILE_MAX_SIZE, //5MB
            'banner_file' => 'image|max:' . Video::BANNER_FILE_MAX_SIZE, //10MB
            'trailer_file' => 'mimetypes:video/mp4|max:' . Video::TRAILER_FILE_MAX_SIZE, //1GB
            'video_file' => 'mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_SIZE, //50GB
        ];
        return $this->rules;
    }

    protected function ruleStore()
    {

        return $this->rules();
    }

    protected function ruleUpdate()
    {
        return $this->rules();
    }

    public function store(Request $request)
    {
        $this->request = $request;
        $validatedData = $this->validate($request, $this->ruleStore());
        /** @var Video $obj */
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        //return ['message' => 'OK'];
        $this->request = $request;
        $obj = $this->findOrFail($id);
        $validatedData = $this->validate(
            $request,
            $request->isMethod('PUT') ? $this->ruleStore() : $this->rulesPatch()
        );
        $obj->update($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return VideoResource::class;
    }

    public function show($id) //GET
    {
        $this->loadGenreCategories = true;
        return parent::show($id);
    }

    protected function queryBuilder(): Builder
    {
        $queryBuilder =  parent::queryBuilder();
        if ($this->loadGenreCategories) {
            $queryBuilder->with(['genres.categories']);
        }
        return $queryBuilder;
    }
}
