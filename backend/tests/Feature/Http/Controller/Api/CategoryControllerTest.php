<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private $category;
    private $serializedFields = [
        'id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = factory(Category::class)->create();
        $this->category->refresh();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJson(['data'=>[$this->category->toArray()]])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);

        $resource = CategoryResource::collection(collect([$this->category]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
            ->assertJson(['data'=>$this->category->toArray()]);

        //Expected resource
        $resource = new CategoryResource(Category::find($response->json('data.id')));
        $this->assertResource($response, $resource);
    }

    public function testInvalidationData()
    {

        // //////////////////////////////////////////////
        $data = ['name'=>''];
        $this->assertInvalidationInStoreAction($data,'validation.required');
        $this->assertInvalidationInUpdateAction($data,'validation.required');
        // //////////////////////////////////////////////
        $data = ['name'=> str_repeat('a',256)];
        $this->assertInvalidationInStoreAction($data,'validation.max.string', ['max'=>255]);
        $this->assertInvalidationInUpdateAction($data,'validation.max.string', ['max'=>255]);
        //////////////////////////////////////////////////////
        $data = ['is_active'=> 'a'];
        $this->assertInvalidationInStoreAction($data,'validation.boolean');
        $this->assertInvalidationInUpdateAction($data,'validation.boolean');
        //////////////////////////////////////////////////////
        
    }

    public function testStore(){
        $data = [
            'name'=>'test'
        ];
        $response = $this->assertStore(
            $data, 
            $data + ['description'=>null, 'is_active'=>true, 'deleted_at'=>null ]
        );
        $response->assertJsonStructure([
            'data'=> $this->serializedFields
        ]);
        //////////////////////////////////////////////qq
        $data = [
            'name'=>'test',
            'description'=>'description',
            'is_active'=>false
        ];
        $this->assertStore(
            $data, 
            $data + ['description'=>'description', 'is_active'=>false ]
        );

        //Expected resource
        $resource = new CategoryResource(Category::find($response->json('data.id')));
        $this->assertResource($response, $resource);
    }

    public function testUpdate(){

        $this->category = factory(Category::class)->create([
            'description'=>'description',
            'is_active'=>false
        ]);
        $data = [
            'name'=>'test',
            'description'=>'test',
            'is_active'=>true
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at'=>null]);
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);
        //Expected resource
        $resource = new CategoryResource(Category::find($response->json('data.id')));
        $this->assertResource($response, $resource);

        $data = [
            'name'=>'test',
            'description'=>''
        ];
        $this->assertUpdate($data, array_merge($data, ['description'=>null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['description'=>'test']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description'=>null]));
    }

    public function testDestroy()
    {

        //checking if there is
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response
            ->assertStatus(200)
            ->assertJson(['data'=>$this->category->toArray()]);

        //destroying
        $response = $this->json('DELETE',route('categories.destroy',['category'=>$this->category->id]), []);
        $response->assertStatus(204);

        //checking if it was destroyed
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response
            ->assertStatus(404);

        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category'=>$this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
