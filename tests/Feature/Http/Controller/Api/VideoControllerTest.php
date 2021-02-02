<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Request;
use SebastianBergmann\Environment\Console;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;
    private $sendData;
    private $testDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();

        $this->video = factory(Video::class)->create();
        $this->video->categories()->sync([$category->id]);
        $this->video->genres()->sync([$genre->id]);
        $this->video->load(['categories','genres']);

        $this->sendData = [
            'title'=>'title',
            'description' => 'description',
            'year_launched' => 2013,
            'rating'=>Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ];

        $this->testDatabase = $this->sendData;
        unset($this->testDatabase['categories_id']);
        unset($this->testDatabase['genres_id']);

    }

    public function testIndex()
    {
        
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);

        // dump($response->baseResponse->getContent());
        // dd([$this->video->toArray()]);
    }

    public function testRollbackStore()
    {

        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturnTrue([]);

        $request = \Mockery::mock(Request::class);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new \Exception());

        $controller->store($request);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
        // dump($response->baseResponse->getContent());
        // dd([$this->video->toArray()]);
    }

    public function testInvalidationData()
    {

        // //////////////////////////////////////////////
        $data = [
            'title'=>'',
            'description' => '',
            'year_launched' => '',
            'rating'=>'',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data,'validation.required');
        $this->assertInvalidationInUpdateAction($data,'validation.required');        
    }

    public function testInvalidationMax()
    {

        // //////////////////////////////////////////////
        $data = [
            'title'=>str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data,'validation.max.string', ['max'=>255]);
        $this->assertInvalidationInUpdateAction($data,'validation.max.string', ['max'=>255]);        
    }

    public function testInvalidationInteger()
    {

        // //////////////////////////////////////////////
        $data = [
            'duration'=>'a'
        ];
        $this->assertInvalidationInStoreAction($data,'validation.integer');
        $this->assertInvalidationInUpdateAction($data,'validation.integer');        
    }

    public function testInvalidationYearLaunchedField()
    {

        // //////////////////////////////////////////////
        $data = [
            'year_launched'=>'a'
        ];
        $this->assertInvalidationInStoreAction($data,'validation.date_format', ['format'=>'Y']);
        $this->assertInvalidationInUpdateAction($data,'validation.date_format', ['format'=>'Y']);        
    }

    public function testInvalidationOpenedField()
    {

        // //////////////////////////////////////////////
        $data = [
            'opened'=>'a'
        ];
        $this->assertInvalidationInStoreAction($data,'validation.boolean');
        $this->assertInvalidationInUpdateAction($data,'validation.boolean');        
    }

    public function testInvalidationRatingField()
    {

        // //////////////////////////////////////////////
        $data = [
            'rating'=>0
        ];
        $this->assertInvalidationInStoreAction($data,'validation.in');
        $this->assertInvalidationInUpdateAction($data,'validation.in');        
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

    public function testInvalidationGenresIdField()
    {
        $data = [
            'genres_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data,'validation.array');
        $this->assertInvalidationInUpdateAction($data,'validation.array'); 
        $data = [
            'genres_id' => [123]
        ];
        $this->assertInvalidationInStoreAction($data,'validation.exists');
        $this->assertInvalidationInUpdateAction($data,'validation.exists');
    }

    public function testStore(){

        $this->assertStore(
            $this->sendData, 
            $this->testDatabase + ['opened'=>false]
        );

        $this->assertStore(
            $this->sendData + ['opened'=>true], 
            $this->testDatabase + ['opened'=>true]
        );

        $response = $this->assertStore(
            $this->sendData + ['rating'=>Video::RATING_LIST[1]], 
            $this->testDatabase + ['rating'=>Video::RATING_LIST[1]]
        );

        $response->assertJsonStructure([
            'created_at','updated_at'
        ]);
    }

    public function testUpdate(){

        $this->assertUpdate(
            $this->sendData, 
            $this->testDatabase + ['opened'=> $this->video->opened ]
        );

        $this->assertUpdate(
            $this->sendData + ['opened'=>true], 
            $this->testDatabase + ['opened'=>true]
        );

        $response = $this->assertUpdate(
            $this->sendData + ['rating'=>Video::RATING_LIST[1]], 
            $this->testDatabase + ['rating'=>Video::RATING_LIST[1]]
        );

        $response->assertJsonStructure([
            'created_at','updated_at'
        ]);
    }

    public function testDestroy()
    {
        //destroying
        $response = $this->json('DELETE',route('videos.destroy',['video'=>$this->video->id]), []);
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video'=>$this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
