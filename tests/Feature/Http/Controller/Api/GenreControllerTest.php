<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testInvalidationData()
    {

        // POST //////////////////////////////////////////////
        $response = $this->json('POST',route('genres.store'), []);
        $this->assertInvalidationRequired($response);
        //////////////////////////////////////////////////////
        $response = $this->json('POST',route('genres.store'), [
            'name'=> str_repeat('a',256),
            'is_active'=> 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        // PUT //////////////////////////////////////////////
        $genre = factory(Genre::class)->create();
        $response = $this->json('PUT',route('genres.update', ['genre'=>$genre->id]), []);
        $this->assertInvalidationRequired($response);
        //////////////////////////////////////////////////////
        $response = $this->json('PUT',route('genres.update', ['genre'=>$genre->id]), [
            'name'=> str_repeat('a',256),
            'is_active'=> 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                trans('validation.required', ['attribute'=>'name'])
            ]);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                trans('validation.max.string', ['attribute'=>'name', 'max'=>255])
            ]);
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                trans('validation.boolean', ['attribute'=>'is active'])
            ]);
    }

    public function testStore(){
        $response = $this->json('POST',route('genres.store'), [
            'name'=>'test'
        ]);

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST',route('genres.store'), [
            'name'=>'test',
            'description'=>'description',
            'is_active'=>false
        ]);

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertFalse($response->json('is_active'));
        $this->assertEquals('description', $response->json('description'));
    }

    public function testUpdate(){

        $genre = factory(Genre::class)->create([
            'description'=>'description',
            'is_active'=>false
        ]);

        $response = $this->json('PUT',route('genres.update',['genre'=>$genre->id]), [
            'name'=>'test',
            'description'=>'test',
            'is_active'=>true
        ]);

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'description'=>'test',
                'is_active'=>true
            ]);
        /////////////////////////////////////////////////////q
        $response = $this->json('PUT',route('genres.update',['genre'=>$genre->id]), [
            'name'=>'test',
            'description'=>'',
            'is_active'=>true
        ]);
        $response->assertJsonFragment([
            'description'=>null,
        ]);
    }

    public function testDestroy()
    {
        //Creating a new category
        $genre = factory(Genre::class)->create();

        //checking if there is
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));
        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());

        //destroying
        $response = $this->json('DELETE',route('genres.destroy',['genre'=>$genre->id]), []);
        $response->assertStatus(204);

        //checking if it was destroyed
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));
        $response
            ->assertStatus(404);
    }
}
