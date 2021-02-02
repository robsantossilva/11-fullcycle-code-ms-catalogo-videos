<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
  private $rules;

  public function __construct()
  {
    $this->rules = [
      'title'=>'required|max:255',
      'description' => 'required',
      'year_launched' => 'required|date_format:Y',
      'opened' => 'boolean',
      'rating'=>'required|in:'. implode(',',Video::RATING_LIST),
      'duration' => 'required|integer',
      'categories_id' => 'required|array|exists:categories,id',
      'genres_id' => 'required|array|exists:genres,id'
    ];
  }

  public function index(Request $request)//GET
  {
      if($request->has('only_trashed')){
          #return $this->model()::onlyTrashed()->get();
          return $this->model()::with(['categories','genres'])->onlyTrashed()->get();
      }

      return $this->model()::with(['categories','genres'])->get();
  }

  public function show($id) //GET
  {
      $obj = $this->findOrFail($id)->load(['categories','genres']);
      return $obj;
  }

  public function store(Request $request)
  {
      $validatedData = $this->validate($request, $this->ruleStore());
      /** @var Video $obj */
      $self = $this;
      $obj = \DB::transaction(function () use ($request, $validatedData, $self) {
        $obj = $this->model()::create($validatedData);
        $self->handleRelations($obj, $request);
        return $obj;
      });
      $obj->refresh();
      return $obj;
  }

  public function update(Request $request, $id)
  {
      $obj = $this->findOrFail($id);
      $validatedData = $this->validate($request, $this->ruleStore());
      $self = $this;
      $obj = \DB::transaction(function ()  use ($obj, $request, $validatedData, $self) {
        $obj->update($validatedData);
        $self->handleRelations($obj, $request);
        return $obj;
      });      
      $obj->refresh();
      return $obj;
  }

  protected function handleRelations(Video $video, Request $request) {
    $video->categories()->sync($request->get('categories_id'));
    $video->genres()->sync($request->get('genres_id'));
  }
    
  protected function model()
  {
    return Video::class;
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
