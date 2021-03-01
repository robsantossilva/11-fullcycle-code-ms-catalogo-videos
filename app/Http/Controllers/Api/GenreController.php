<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
  private $rules = [
    'name'=>'required|max:255',
    'description'=>'nullable',
    'is_active'=>'boolean',
    'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
  ];

  protected function relatedTables() : array
  {
    return Genre::RELATED_TABLES;
  }

  protected function model()
  {
    return Genre::class;
  }

  protected function ruleStore()
  {
    return $this->rules;
  }

  protected function ruleUpdate()
  {
    return $this->rules;
  }
}
