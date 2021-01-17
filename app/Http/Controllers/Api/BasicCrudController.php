<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;

abstract class BasicCrudController extends Controller
{

    protected abstract function model();

    protected abstract function ruleStore();

    protected abstract function ruleUpdate();

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)//GET
    {
        if($request->has('only_trashed')){
            return $this->model()::onlyTrashed()->get();
        }

        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->ruleStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        return $obj;
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id) //GET
    {
        $obj = $this->findOrFail($id);
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->ruleStore());
        $obj->update($validatedData);
        $obj->refresh();
        return $obj;
    }

    public function destroy($id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();
        return response()->noContent();//204 - No Content
    }
}
