<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\ResourceCollection;
use EloquentFilter\Filterable;

abstract class BasicCrudController extends Controller
{

    /**
     * @var Request $request
     */
    protected $request;

    protected $defaultPerPage = 15;

    protected abstract function model();

    protected abstract function ruleStore();

    protected abstract function ruleUpdate();

    protected abstract function relatedTables(): array;

    protected abstract function resource();

    protected abstract function resourceCollection();

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) //GET
    {

        $perPage = (int) $request->get('per_page', $this->defaultPerPage);
        $hasFilter = in_array(Filterable::class, class_uses($this->model()));

        $query = $this->queryBuilder();

        if ($hasFilter) {
            $query = $query->filter($request->all());
        }

        $data = $request->has('all') || !$this->defaultPerPage
            ? $query->get()
            : $query->paginate($perPage);

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

        if ($this->relatedTables()) {
            $self = $this;
            $obj = \DB::transaction(function () use ($request, $validatedData, $self) {
                $obj = $this->queryBuilder()->create($validatedData);
                $self->handleRelations($obj, $request);
                return $obj;
            });
            $obj->load(array_keys($this->relatedTables()))->refresh();
            $resource = $this->resource();
            return new $resource($obj);
        }

        $obj = $this->queryBuilder()->create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->queryBuilder()->where($keyName, $id)->firstOrFail();
    }

    public function show($id) //GET
    {
        /*if($this->relatedTables())
        {
            $obj = $this->findOrFail($id)->load(array_keys($this->relatedTables()));
        }else{*/
        $obj = $this->findOrFail($id);
        //}

        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $this->request = $request;

        $obj = $this->findOrFail($id);
        $validatedData = $this->validate(
            $request,
            $request->isMethod('PUT') ? $this->ruleStore() : $this->rulesPatch()
        );

        if ($this->relatedTables()) {
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

    protected function rulesPatch()
    {
        return array_map(function ($rules) {
            if (is_array($rules)) {
                $exists = in_array("required", $rules);
                if ($exists) {
                    array_unshift($rules, "sometimes");
                }
            } else {
                return str_replace("required", "sometimes|required", $rules);
            }
            return $rules;
        }, $this->ruleUpdate());
    }

    public function destroy($id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();
        return response()->noContent(); //204 - No Content
    }

    public function destroyCollection(Request $request)
    {
        $data = $this->validateIds($request);
        $this->model()::WhereIn('id', $data['ids'])->delete();
        return response()->noContent();
    }

    protected function validateIds(Request $request)
    {
        $model = $this->model();
        $ids = explode(',', $request->get('ids'));
        $validator = \Validator::make(
            [
                'ids' => $ids
            ],
            [
                'ids' => 'required|exists:' . (new $model)->getTable() . ',id'
            ]
        );
        return $validator->validate();
    }

    protected function handleRelations($obj, Request $request)
    {
        foreach ($this->relatedTables() as $table => $field) {
            $obj->$table()->sync($request->get($field));
        }
    }

    protected function queryBuilder(): Builder
    {
        return $this->model()::query();
    }
}
