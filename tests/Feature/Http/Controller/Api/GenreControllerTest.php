<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();

        $this->genre = factory(Genre::class)->create();
        $this->genre->categories()->sync([$this->category->id]);
        $this->genre->load(array_keys(Genre::RELATED_TABLES))->refresh();
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));
        $response
            ->assertStatus(200)
            ->assertJsonFragment([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());

        $this->assertEquals($response->baseResponse->content(), json_encode($this->genre->toArray()));
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

    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data,'validation.array');
        $this->assertInvalidationInUpdateAction($data,'validation.array'); 
        $data = [
            'categories_id' => [123]
        ];
        $this->assertInvalidationInStoreAction($data,'validation.exists');
        $this->assertInvalidationInUpdateAction($data,'validation.exists');
    }

    public function testStore(){
        $data = [
            'name'=>'test'
        ];
        $response = $this->assertStore(
            $data + ['categories_id'=>[$this->category->id]], 
            $data + ['description'=>null, 'is_active'=>true, 'deleted_at'=>null ]
        );
        $response->assertJsonStructure([
            'created_at','updated_at'
        ]);
        //////////////////////////////////////////////qq
        $data = [
            'name'=>'test',
            'description'=>'description',
            'is_active'=>false
        ];
        
        $response = $this->assertStore(
            $data + ['categories_id'=>[$this->category->id]], 
            $data + ['description'=>'description', 'is_active'=>false ]
        );

        $this->assertGenreHasCategory($response);
    }

    public function testUpdate(){

        $this->genre = factory(Genre::class)->create([
            'description'=>'description',
            'is_active'=>false
        ]);
        $data = [
            'name'=>'test',
            'description'=>'test',
            'is_active'=>true
        ];
        $response = $this->assertUpdate(
            $data + ['categories_id'=>[$this->category->id]], 
            $data + ['deleted_at'=>null]
        );
        $response->assertJsonStructure([
            'created_at','updated_at'
        ]);

        $data = [
            'name'=>'test',
            'description'=>''
        ];
        $this->assertUpdate(
            $data + ['categories_id'=>[$this->category->id]], 
            array_merge($data, ['description'=>null])
        );

        $data['description'] = 'test';
        $this->assertUpdate(
            $data + ['categories_id'=>[$this->category->id]],
            array_merge($data, ['description'=>'test'])
        );

        $data['description'] = null;
        $this->assertUpdate(
            $data + ['categories_id'=>[$this->category->id]],
            array_merge($data, ['description'=>null])
        );

        #####################################################
        $this->category = factory(Category::class)->create();
        $data = [
            'name'=>'test',
            'description'=>'test',
            'is_active'=>true
        ];
        $response = $this->assertUpdate(
            $data + ['categories_id'=>[$this->category->id]],
            $data
        );
        $this->assertGenreHasCategory($response);
    }

    protected function assertGenreHasCategory($response)
    {
        $response = json_decode($response->baseResponse->content());
        $hasCategory = false;
        foreach($response->categories as $category){
            $hasCategory = $category->id == $this->category->id ? true : false;
        }
        $this->assertTrue($hasCategory);
    }

    public function testDestroy()
    {
        //checking if there is
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());

        //destroying
        $response = $this->json('DELETE',route('genres.destroy',['genre'=>$this->genre->id]), []);
        $response->assertStatus(204);

        //checking if it was destroyed
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));
        $response
            ->assertStatus(404);

        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre'=>$this->genre->id]);
    }

    protected function model()
    {
        return Genre::class;
    }
}
