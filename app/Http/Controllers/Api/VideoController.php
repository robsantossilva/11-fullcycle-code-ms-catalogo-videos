<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Video;
use App\Rules\CategoryGenreLinked;
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

  protected function ruleStore()
  {
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
        'exists:categories,id',
        new CategoryGenreLinked($this->request)
      ],
      'genres_id' => [
        'required',
        'array',
        'exists:genres,id',
        new CategoryGenreLinked($this->request)
      ]
    ];
    return $this->rules;
  }

  protected function ruleUpdate()
  {
    return $this->rules;
  }
}
