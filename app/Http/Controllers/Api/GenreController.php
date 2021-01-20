<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;

class GenreController extends BasicCrudController
{
  private $rules = [
    'name'=>'required|max:255',
    'description'=>'nullable',
    'is_active'=>'boolean'
  ];

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
    // private $rules = [
    //     'name'=>'required|max:255',
    //     'is_active'=>'boolean'
    // ];

    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function index(Request $request)
    // {
    //     if($request->has('only_trashed')){
    //         return Genre::onlyTrashed()->get();
    //     }
    //     return Genre::all();
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(Request $request)
    // {
    //     $this->validate($request, $this->rules);
    //     $genre = Genre::create($request->all());
    //     $genre->refresh();
    //     return $genre;
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  \App\Models\Genre  $genre
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show(Genre $genre)
    // {
    //     return $genre;
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\Genre  $genre
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, Genre $genre)
    // {
    //     $this->validate($request, $this->rules);
    //     $genre->update($request->all());
    //     return $genre;
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Models\Genre  $genre
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy(Genre $genre)
    // {
    //     $genre->delete();
    //     return response()->noContent();//204 - No Content
    // }
}
