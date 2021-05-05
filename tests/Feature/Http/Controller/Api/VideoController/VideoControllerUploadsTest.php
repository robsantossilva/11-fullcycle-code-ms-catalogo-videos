<?php

namespace Tests\Feature\Http\Controller\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Arr;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Http\Controller\Api\VideoController\BaseVideoControllerTestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestUploads;

    public function testInvalidationThumbField()
    {
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            Video::THUMB_FILE_MAX_SIZE,
            'validation.image'
        );
    }

    public function testInvalidationBannerField()
    {
        $this->assertInvalidationFile(
            'banner_file',
            'png',
            Video::BANNER_FILE_MAX_SIZE,
            'validation.image'
        );
    }

    public function testInvalidationTrailerField()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'validation.mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'validation.mimetypes', ['values' => 'video/mp4']
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
        $this->assertFilesOnPersist($response, $files);
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
            $this->sendData + ['categories_id' => [$category->id],'genres_id' => [$genre->id]] +
            $files
        );
        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);

        $newFiles = [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpg'),
        ];
        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + ['categories_id' => [$category->id],'genres_id' => [$genre->id]] +
            $newFiles
        );
        $response->assertStatus(200);
        $this->assertFilesOnPersist(
            $response, 
            Arr::except($files, ['thumb_file', 'video_file']) + $newFiles
        );

        $id = $response->json('id');
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
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

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpg'),
            'banner_file' => UploadedFile::fake()->create('banner_file.png'),
            'trailer_file' => UploadedFile::fake()->create('trailer_file.mp4')
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
