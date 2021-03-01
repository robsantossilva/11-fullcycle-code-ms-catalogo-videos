<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Video;
use App\Rules\CategoryGenreLinked;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
  private $rules;

  public function __construct(){}

  protected function relatedTables() : array
  {
    return Video::RELATED_TABLES;
  }
    
  protected function model()
  {
    return Video::class;
  }

  protected function rules() {

    $categoriesId = $this->request->get('categories_id');
    $categoriesId = is_array($categoriesId) ? $categoriesId : [];

    $this->rules = [
      'title'=>'required|max:255',
      'description' => 'required',
      'year_launched' => 'required|date_format:Y',
      'opened' => 'boolean',
      'rating'=>'required|in:'. implode(',',Video::RATING_LIST),
      'duration' => 'required|integer',
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
      ]
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
}
