<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;
    private $sendData;
    private $testDatabase;
    private $category;
    private $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = factory(Category::class)->create();
        $this->genre = factory(Genre::class)->create();

        $this->video = factory(Video::class)->create();
        $this->video->categories()->sync([$this->category->id]);
        $this->video->genres()->sync([$this->genre->id]);
        $this->video->load(['categories','genres']);

        $this->sendData = [
            'title'=>'title',
            'description' => 'description',
            'year_launched' => 2013,
            'rating'=>Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id]
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
    }

    public function testRollbackStore()
    {

        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller
            ->shouldReceive('ruleStore')
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        try {
            $controller->store($request);
        } catch (TestException $exception) {
            $this->assertCount(1, Video::all());
        }
        
    }

    public function testRollbackUpdate()
    {

        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller
            ->shouldReceive('ruleStore')
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        try {
            $controller->update($request, $this->video->id);
        } catch (TestException $exception) {
            $this->assertCount(1, Video::all());
        }
        
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

        $this->assertVideoHasCategory($response);
        $this->assertVideoHasGenre($response);
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

        $this->assertUpdate(
            $this->sendData + ['rating'=>Video::RATING_LIST[1]], 
            $this->testDatabase + ['rating'=>Video::RATING_LIST[1]]
        );

        #################################################################
        $this->category = factory(Category::class)->create();
        $this->genre = factory(Genre::class)->create();

        $dataSend = [
            'title'=>'title',
            'description' => 'description',
            'year_launched' => 2013,
            'rating'=>Video::RATING_LIST[0],
            'duration' => 90
        ];
        $response = $this->assertUpdate(
            $dataSend + [
                'categories_id' => [$this->category->id],
                'genres_id' => [$this->genre->id]
            ], 
            $dataSend
        );

        $this->assertVideoHasCategory($response);
        $this->assertVideoHasGenre($response);

        $response->assertJsonStructure([
            'created_at','updated_at'
        ]);
    }

    protected function assertVideoHasCategory($response)
    {
        $response = json_decode($response->baseResponse->content());
        $hasCategory = false;
        foreach($response->categories as $category){
            $hasCategory = $category->id == $this->category->id ? true : false;
        }
        $this->assertTrue($hasCategory);
    }

    protected function assertVideoHasGenre($response)
    {
        $response = json_decode($response->baseResponse->content());
        $hasGenre = false;
        foreach($response->genres as $genre){
            $hasGenre = $genre->id == $this->genre->id ? true : false;
        }
        $this->assertTrue($hasGenre);
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
