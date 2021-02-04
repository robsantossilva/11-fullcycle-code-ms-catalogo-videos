<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use \Ramsey\Uuid\Uuid as RamseyUuid;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

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
                "deleted_at",
                "created_at",
                "updated_at"
            ],
            $videoKey
        );
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
        $video = factory(Video::class)->create([
            'title'=>'test_update_0',
            'year_launched'=>2011,
            'rating'=>Video::RATING_LIST[0],
            'duration'=>10,
            'opened'=>false,
            'description'=>'test0'
        ]);
        
        $video->update([
            'title'=>'test_update_1',
            'year_launched'=>2031,
            'rating'=>Video::RATING_LIST[1],
            'duration'=>20,
            'opened'=>true,
            'description'=>'test2_description'
        ]);
        $video->refresh();
        $this->assertEquals('test_update_1', $video->title);
        $this->assertEquals('test2_description', $video->description);
        $this->assertEquals(2031, $video->year_launched);
        $this->assertTrue($video->opened);
        $this->assertEquals(20, $video->duration);
        $this->assertEquals(Video::RATING_LIST[1], $video->rating);
    }
}
