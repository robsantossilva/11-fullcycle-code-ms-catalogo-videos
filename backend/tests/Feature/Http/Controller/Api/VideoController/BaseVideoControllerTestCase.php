<?php

namespace Tests\Feature\Http\Controller\Api\VideoController;

use App\Models\CastMember;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $video;
    protected $sendData;
    protected $testDatabase;
    protected $category;
    protected $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = factory(Category::class)->create();
        $this->genre = factory(Genre::class)->create();
        $this->genre->categories()->sync([$this->category->id]);
        $this->genre->load(array_keys(Genre::RELATED_TABLES))->refresh();
        $this->castMember = factory(CastMember::class)->create();

        $this->video = factory(Video::class)->create(['opened' => false]);
        $this->video->categories()->sync([$this->category->id]);
        $this->video->genres()->sync([$this->genre->id]);
        $this->video->castMembers()->sync([$this->castMember->id]);
        $this->video->load(array_keys(Video::RELATED_TABLES))->refresh();



        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2013,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [$this->category->id],
            'genres_id' => [$this->genre->id],
            'cast_members_id' => [$this->castMember->id]
        ];

        $this->testDatabase = $this->sendData;
        unset($this->testDatabase['categories_id']);
        unset($this->testDatabase['genres_id']);
        unset($this->testDatabase['cast_members_id']);
    }

    protected function assertIfFilesUrlExists(Video $video, TestResponse $response)
    {
        $fileFields = Video::$fileFields;
        $data = $response->json('data');
        $data = array_key_exists(0, $data) ? $data[0] : $data;
        foreach ($fileFields as $field) {
            $file = $video->{$field};
            $this->assertEquals(
                \Storage::url($video->relativeFilePath($file)),
                $data[$field . '_url']
            );
        }
    }
}
