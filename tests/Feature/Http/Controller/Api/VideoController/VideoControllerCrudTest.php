<?php

namespace Tests\Feature\Http\Controller\Api\VideoController;

use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Tests\Feature\Http\Controller\Api\VideoController\BaseVideoControllerTestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestResources, TestResources;

    private $serializedFields = [
        'id',
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file_url',
        'thumb_file_url',
        'banner_file_url',
        'trailer_file_url',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'deleted_at',
                'created_at',
                'updated_at'                
            ]
        ],
        'genres' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'deleted_at',
                'created_at',
                'updated_at',
            ]
        ]
    ];

    public function testIndex()
    {
        
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            //->assertJsonFragment(['data'=>[$this->video->toArray()]])
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);
        $resource = VideoResource::collection(collect([$this->video]));
        $this->assertResource($response, $resource);

        $this->assertIfFilesUrlExists($this->video, $response);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
            ->assertJson(['data'=>$this->video->toArray()]);

        //Expected resource
        $resource = new VideoResource(Video::find($response->json('data.id')));
        $this->assertResource($response, $resource);

        $this->assertIfFilesUrlExists($this->video, $response);
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

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
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

        $genre = factory(Genre::class)->create();
        $genre->delete();
        $data = [
            'categories_id' => [$genre->id]
        ];
        $this->assertInvalidationInStoreAction($data,'validation.exists');
        $this->assertInvalidationInUpdateAction($data,'validation.exists');
    }

    public function testInvalidationGenresNotLinked()
    {
        $this->category = factory(Category::class)->create();
        $this->genre = factory(Genre::class)->create();

        // $this->genre = factory(Genre::class)->create();
        // $this->genre->categories()->sync([$this->category->id]);
        // $this->genre->load(array_keys(Genre::RELATED_TABLES))->refresh();

        $data = [
            'genres_id' => [$this->genre->id]
        ];

        $this->assertInvalidationInStoreAction($data,'validation.categorygenrelinked');
        $this->assertInvalidationInUpdateAction($data,'validation.categorygenrelinked');
    }

    public function testSaveWithoutFiles()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $sendData = [
            'title'=>'title',
            'description' => 'description',
            'year_launched' => 2013,
            'rating'=>Video::RATING_LIST[0],
            'duration' => 90
        ];

        $data = [
            [
                'send_data'=> $sendData + [
                    'opened'=>false,
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id],
                ],
                'test_data' => $sendData + ['opened'=>false]
            ],
            [
                'send_data'=> $sendData + [
                    'opened'=>true,
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id],
                ],
                'test_data' => $sendData + ['opened'=>true]
            ],
            [
                'send_data'=> $sendData + [
                    'rating'=> Video::RATING_LIST[1],
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id],
                ],
                'test_data' => $sendData + ['rating'=> Video::RATING_LIST[1]]
            ]
        ];

        foreach($data as $key => $value){
            $response = $this->assertStore(
                $value['send_data'],
                $value['test_data'] + ['deleted_at'=>null]
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $resource = new VideoResource(Video::find($response->json('data.id')));
            $this->assertResource(
                $response,
                $resource
            );
            $this->assertHasCategory(
                $response->json('data.id'),
                $value['send_data']['categories_id'][0]
            );
            $this->assertHasGenre(
                $response->json('data.id'),
                $value['send_data']['genres_id'][0]
            );

            /////// --------------------

            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at'=>null]
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource(
                $response,
                new VideoResource(Video::find($response->json('data.id')))
            );
            $this->assertHasCategory(
                $response->json('data.id'),
                $value['send_data']['categories_id'][0]
            );
            $this->assertHasGenre(
                $response->json('data.id'),
                $value['send_data']['genres_id'][0]
            );
        }

    }

    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video',[
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video',[
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);
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
            'data' => $this->serializedFields
        ]);

        $this->assertVideoHasCategory($response);
        $this->assertVideoHasGenre($response);

        $this->assertResource(
            $response,
            new VideoResource(Video::find($response->json('data.id')))
        );
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
        $this->genre->categories()->sync([$this->category->id]);
        $this->genre->load(array_keys(Genre::RELATED_TABLES))->refresh();

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
            'data' => $this->serializedFields
        ]);
    }

    protected function assertVideoHasCategory($response)
    {
        $response = json_decode($response->baseResponse->content());
        $hasCategory = false;
        foreach($response->data->categories as $category){
            $hasCategory = $category->id == $this->category->id ? true : false;
        }
        $this->assertTrue($hasCategory);
        $this->assertDatabaseHas('category_video',[
            'video_id'=>$response->data->id,
            'category_id'=>$this->category->id
        ]);
    }

    protected function assertVideoHasGenre($response)
    {
        $response = json_decode($response->baseResponse->content());
        $hasGenre = false;
        foreach($response->data->genres as $genre){
            $hasGenre = $genre->id == $this->genre->id ? true : false;
        }
        $this->assertTrue($hasGenre);
        $this->assertDatabaseHas('genre_video',[
            'video_id'=>$response->data->id,
            'genre_id'=>$this->genre->id
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
