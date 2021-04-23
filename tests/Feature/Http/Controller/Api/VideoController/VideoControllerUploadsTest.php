<?php

namespace Tests\Feature\Http\Controller\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Http\Controller\Api\VideoController\BaseVideoControllerTestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestUploads;

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            50000000,
            'validation.mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testInvalidationThumbField()
    {
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            5000,
            'validation.mimetypes', ['values' => 'jpg, png, jpeg']
        );
    }

    public function testInvalidationBannerField()
    {
        $this->assertInvalidationFile(
            'banner_file',
            'png',
            10000,
            'validation.mimetypes', ['values' => 'jpg, png, jpeg']
        );
    }

    public function testInvalidationTrailerField()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'jpeg',
            1000000,
            'validation.mimetypes', ['values' => 'jpg, png, jpeg']
        );
    }

    public function testStoreWithFiles()
    {
        UploadedFile::fake()->image("image.jpg");
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData +
            [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id],
            ] +
            $files
        );

        $response->assertStatus(201);
        $id = $response->json('id');
        foreach($files as $file){
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData +
            [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id],
            ] +
            $files
        );

        $response->assertStatus(200);
        $id = $response->json('id');
        foreach($files as $file){
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpg'),
            'banner_file' => UploadedFile::fake()->create('banner_file.png'),
            'trailer_file' => UploadedFile::fake()->create('trailer_file.jpeg')
        ];
    }

    protected function assertVideoHasCategory($response)
    {
        $response = json_decode($response->baseResponse->content());
        $hasCategory = false;
        foreach($response->categories as $category){
            $hasCategory = $category->id == $this->category->id ? true : false;
        }
        $this->assertTrue($hasCategory);
        $this->assertDatabaseHas('category_video',[
            'video_id'=>$response->id,
            'category_id'=>$this->category->id
        ]);
    }

    protected function assertVideoHasGenre($response)
    {
        $response = json_decode($response->baseResponse->content());
        $hasGenre = false;
        foreach($response->genres as $genre){
            $hasGenre = $genre->id == $this->genre->id ? true : false;
        }
        $this->assertTrue($hasGenre);
        $this->assertDatabaseHas('genre_video',[
            'video_id'=>$response->id,
            'genre_id'=>$this->genre->id
        ]);
    }

    public function testUploadVideoFile()
    {
        \Storage::fake();
        $videoFile = UploadedFile::fake()->create('video.mp4')->size(2000);
        $response = $this->assertStore(
            $this->sendData + ['video_file'=> $videoFile ], 
            $this->testDatabase
        );

        $videoId = json_decode($response->baseResponse->getContent())->id;

        \Storage::assertExists("{$videoId}/{$videoFile->hashName()}");
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
