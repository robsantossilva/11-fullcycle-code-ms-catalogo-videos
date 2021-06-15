<?php 
namespace Tests\Feature\Models\Video;

use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\QueryException;
use \Ramsey\Uuid\Uuid as RamseyUuid;

class VideoCrudTest extends BaseVideoTestCase
{
    public function testRollbackCreate()
    {
        $hasError = false;
        try {
            Video::create([
                'title'=>'title',
                'description' => 'description',
                'year_launched' => 2013,
                'rating'=>Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0,1,2],
                'genres_id' => [0,1,2]
            ]);
        } catch (QueryException $exception) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
        
    }

    public function testRollbackUpdate()
    {
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;
        $hasError = false;
        try {
            $video->update([
                'title'=>'title',
                'description' => 'description',
                'year_launched' => 2013,
                'rating'=>Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0,1,2],
                'genres_id' => [0,1,2]
            ]);
        } catch (QueryException $exception) {
            $this->assertDatabaseHas('videos',[
                'title'=>$oldTitle
            ]);
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testList()
    {
        factory(Video::class,1)->create();

        $videos = Video::all();
        $this->assertCount(1,$videos);

        $video = $videos->first();
        $videoKey = array_keys($video->getAttributes());

        $this->assertEqualsCanonicalizing(
            [
                "id",
                'title',
                'description',
                'year_launched',
                'opened',
                'rating',
                'duration',
                'video_file',
                'thumb_file',
                'banner_file',
                'trailer_file',
                "deleted_at",
                "created_at",
                "updated_at"
            ],
            $videoKey
        );
    }

    public function testCreateWithBasicFields()
    {
        $fileFields = [];
        foreach(Video::$fileFields as $field){
            $fileFields[$field] = "{$field}.test";
        }

        $video = Video::create($this->data + $fileFields);
        $video->refresh();

        $this->assertEquals(36, strlen($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + $fileFields + ['opened'=>false]);
        
        $video = Video::create($this->data + ['opened'=> true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened'=>true]);

    }

    public function testCreateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = Video::create($this->data + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function assertHasCategory($videoId, $categoryId){
        $this->assertDatabaseHas('category_video',[
            'video_id'=> $videoId,
            'category_id'=> $categoryId
        ]);
    }

    public function assertHasGenre($videoId, $genreId){
        $this->assertDatabaseHas('genre_video',[
            'video_id'=> $videoId,
            'genre_id'=> $genreId
        ]);
    }

    public function testUpdateWithBasicFields()
    {
        $fileFields = [];
        foreach(Video::$fileFields as $field){
            $fileFields[$field] = "{$field}.test";
        }

        $video = factory(Video::class)->create(['opened'=>false]);
        $video->update($this->data + $fileFields);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + $fileFields + ['opened'=>false]);
        
        $video = factory(Video::class)->create(['opened'=>false]);
        $video->update($this->data + $fileFields + ['opened'=>true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened'=>true]);

    }

    public function testCreate()
    {
        $video = Video::create([
            'title'=>'test1',
            'year_launched'=>2021,
            'rating'=>Video::RATING_LIST[0],
            'duration'=>10,
        ]);
        $video->refresh();
        $this->assertEquals('test1', $video->title);
        $this->assertNull($video->description);
        $this->assertEquals(2021, $video->year_launched);
        $this->assertFalse($video->opened);
        $this->assertEquals(10, $video->duration);
        $this->assertEquals(Video::RATING_LIST[0], $video->rating);
        //////////////////////////////////////////////////////////
        $video = Video::create([
            'title'=>'test1',
            'year_launched'=>2021,
            'rating'=>Video::RATING_LIST[0],
            'duration'=>10,
            'opened'=>true
        ]);
        $video->refresh();
        $this->assertTrue($video->opened);
        //////////////////////////////////////////////////////////
        $video = Video::create([
            'title'=>'test1',
            'year_launched'=>2021,
            'rating'=>Video::RATING_LIST[0],
            'duration'=>10,
            'opened'=>true,
            'description'=>'test2_description'
        ]);
        $video->refresh();
        $this->assertEquals('test2_description', $video->description);
        // //////////////////////////////////////////////////////////
        $uuid = RamseyUuid::fromString($video->id);
        $this->assertEquals(RamseyUuid::UUID_TYPE_RANDOM,$uuid->getVersion());
    }

    public function testUpdate()
    {
        $video = factory(Video::class)->create();
        
        $video->update([
            'title'=>'test_update_1',
            'year_launched'=>2031,
            'rating'=>Video::RATING_LIST[1],
            'duration'=>20,
            'opened'=>true,
            'description'=>'test2_description'
        ]);
        $video->refresh();

        $this->assertDatabaseHas('videos',[
            'title'=>'test_update_1',
            'year_launched'=>2031,
            'rating'=>Video::RATING_LIST[1],
            'duration'=>20,
            'opened'=>true,
            'description'=>'test2_description'
        ]);

        $this->assertEquals('test_update_1', $video->title);
        $this->assertEquals('test2_description', $video->description);
        $this->assertEquals(2031, $video->year_launched);
        $this->assertTrue($video->opened);
        $this->assertEquals(20, $video->duration);
        $this->assertEquals(Video::RATING_LIST[1], $video->rating);
    }

    public function testUpdateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = factory(Video::class)->create();

        $video->update($this->data + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testHandleRelations()
    {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $video->refresh();
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$category->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, [
            'genres_id' => [$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->genres);

        $video->categories()->delete();
        $video->genres()->delete();

        Video::handleRelations($video,[
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->genres);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();

        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[0]]
        ]);
        $this->assertDatabaseHas('category_video',[
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[1],$categoriesId[2]]
        ]);
        $this->assertDatabaseMissing('category_video',[
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video',[
            'category_id' => $categoriesId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video',[
            'category_id' => $categoriesId[2],
            'video_id' => $video->id
        ]);
    }

    public function testSyncGenres()
    {
        $genres = factory(Genre::class, 3)->create();
        $genresId = $genres->pluck('id')->toArray();       
        $video = factory(Video::class)->create();

        Video::handleRelations($video, [
            'genres_id' => [$genresId[0]]
        ]);
        $this->assertDatabaseHas('genre_video',[
            'video_id' => $video->id,
            'genre_id' => [$genresId[0]],
        ]);

        Video::handleRelations($video, [
            'genres_id' => [$genresId[1],$genresId[2]]
        ]);
        $this->assertDatabaseMissing('genre_video',[
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video',[
            'genre_id' => $genresId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video',[
            'genre_id' => $genresId[2],
            'video_id' => $video->id
        ]);
    }
}