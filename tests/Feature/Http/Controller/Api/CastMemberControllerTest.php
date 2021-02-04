<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    }

    public function testIndex()
    {
        
        $response = $this->get(route('cast_members.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
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
        $data = ['type'=> 'a'];
        $this->assertInvalidationInStoreAction($data,'validation.integer');
        $this->assertInvalidationInUpdateAction($data,'validation.integer');
        //////////////////////////////////////////////////////

        $data = ['type'=> 'a'];
        $this->assertInvalidationInStoreAction($data,'validation.in');
        $this->assertInvalidationInUpdateAction($data,'validation.in');
        //////////////////////////////////////////////////////
        
    }

    public function testStore(){
        $data = [
            'name'=>'test',
            'type'=>CastMember::TYPE_ACTOR
        ];
        $response = $this->assertStore(
            $data, 
            $data + ['type'=>CastMember::TYPE_ACTOR, 'deleted_at'=>null ]
        );
        $response->assertJsonStructure([
            'created_at','updated_at'
        ]);
        //////////////////////////////////////////////qq
        $data = [
            'name'=>'test',
            'type'=>CastMember::TYPE_DIRECTOR
        ];
        $this->assertStore(
            $data, 
            $data + ['type'=>'test', 'type'=>CastMember::TYPE_DIRECTOR ]
        );
    }

    public function testUpdate(){

        $this->castMember = factory(CastMember::class)->create([
            'type'=>CastMember::TYPE_DIRECTOR
        ]);
        $data = [
            'name'=>'test',
            'type'=>CastMember::TYPE_ACTOR
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at'=>null]);
        $response->assertJsonStructure([
            'created_at','updated_at'
        ]);

        $data = [
            'name'=>'test',
        ];

        $data['type'] = CastMember::TYPE_DIRECTOR;
        $this->assertUpdate($data, array_merge($data, ['type'=>CastMember::TYPE_DIRECTOR]));

        $data['type'] = CastMember::TYPE_ACTOR;
        $this->assertUpdate($data, array_merge($data, ['type'=>CastMember::TYPE_ACTOR]));
    }

    public function testDestroy()
    {

        //checking if there is
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());

        //destroying
        $response = $this->json('DELETE',route('cast_members.destroy',['cast_member'=>$this->castMember->id]), []);
        $response->assertStatus(204);

        //checking if it was destroyed
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));
        $response
            ->assertStatus(404);

        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->castMember->id));
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member'=>$this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }
}
