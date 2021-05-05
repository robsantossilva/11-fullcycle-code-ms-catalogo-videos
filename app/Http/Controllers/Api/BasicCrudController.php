<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller
{

    /**
     * @var Request $request
     */
    protected $request;

    protected $paginationSize = 15;

    protected abstract function model();

    protected abstract function ruleStore();

    protected abstract function ruleUpdate();

    protected abstract function relatedTables() : array ;

    protected abstract function resource();

    protected abstract function resourceCollection();

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)//GET
    {
        // if($request->has('only_trashed')){
        //     if($this->relatedTables()){
        //         return $this->model()::with(array_keys($this->relatedTables()))->onlyTrashed()->get();
        //     }
        //     return $this->model()::onlyTrashed()->get();
        // }
        
        if($this->relatedTables()){
            $data = !$this->paginationSize 
                ? $this->model()::with(array_keys($this->relatedTables()))->get()
                : $this->model()::with(array_keys($this->relatedTables()))->paginate();
        }else{
            $data = !$this->paginationSize 
                ? $this->model()::all() 
                : $this->model()::paginate($this->paginationSize);
        }
        
        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($resourceCollectionClass);
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data);
    }

    public function store(Request $request)
    {
        $this->request = $request;

        $validatedData = $this->validate($request, $this->ruleStore());
        /** @var Video $obj */

        if($this->relatedTables()){
            $self = $this;
            $obj = \DB::transaction(function () use ($request, $validatedData, $self) {
                $obj = $this->model()::create($validatedData);
                $self->handleRelations($obj, $request);
                return $obj;
            });
            $obj->load(array_keys($this->relatedTables()))->refresh();
            $resource = $this->resource();
            return new $resource($obj);
        }

        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id) //GET
    {
        if($this->relatedTables())
        {
            $obj = $this->findOrFail($id)->load(array_keys($this->relatedTables()));
        }else{
            $obj = $this->findOrFail($id);
        }        

        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $this->request = $request;

        $obj = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->ruleStore());

        if($this->relatedTables()){
            $self = $this;
            $obj = \DB::transaction(function ()  use ($obj, $request, $validatedData, $self) {
                $obj->update($validatedData);
                $self->handleRelations($obj, $request);
                return $obj;
            });      
            $obj->load(array_keys($this->relatedTables()))->refresh();
            $resource = $this->resource();
            return new $resource($obj);
        }

        $obj->update($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function destroy($id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();
        return response()->noContent();//204 - No Content
    }

    protected function handleRelations($obj, Request $request) {
        foreach($this->relatedTables() as $table => $field){
            $obj->$table()->sync($request->get($field));
        }
    }
}
