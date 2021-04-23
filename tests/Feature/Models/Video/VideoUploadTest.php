<?php 
namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;

class VideoUploadTest extends BaseVideoTestCase
{
    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = Video::create(
            $this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->image('video.jpg'),
                'banner_file' => UploadedFile::fake()->image('banner.jpg'),
                'trailer_file' => UploadedFile::fake()->image('trailer.jpg')
            ]
        );
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");
    }

    public function testCreateIfRollbackFiles()
    {
        \Storage::fake();
        \Event::listen(TransactionCommitted::class, function(){
            throw new TestException();
        });

        $hasError = false;
        
        try {
            Video::create(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.jpg'),
                    'banner_file' => UploadedFile::fake()->image('banner.jpg'),
                    'trailer_file' => UploadedFile::fake()->image('trailer.jpg'),
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        $thumbFile = UploadedFile::fake()->image('thumb.jpg');
        $videoFile = UploadedFile::fake()->image('video.jpg');
        $bannerFile = UploadedFile::fake()->image('banner.jpg');
        $trailerFile = UploadedFile::fake()->image('trailer.jpg');

        $video->update(
            $this->data + [
                'thumb_file' => $thumbFile,
                'video_file' => $videoFile,
                'banner_file' => $bannerFile,
                'trailer_file' => $trailerFile
            ]
        );
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");

        $newVideoFile = UploadedFile::fake()->image('video.jpg');
        $newBannerFile = UploadedFile::fake()->image('banner.jpg');
        $video->update(
            $this->data + [
                'video_file' => $newVideoFile,
                'banner_file' => $newBannerFile
            ]
        );
        \Storage::assertExists("{$video->id}/{$thumbFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$trailerFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newBannerFile->hashName()}");

        \Storage::assertMissing("{$video->id}/{$videoFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$bannerFile->hashName()}");
    }

    public function testUpdateIfRollbackFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        \Event::listen(TransactionCommitted::class, function(){
            throw new TestException();
        });

        $hasError = false;
        
        try {
            $video->update(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->create('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.jpg'),
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testFileUrlAttribute(){
        // $this->obj->thumb_file_url = "123.png";
        // $this->assertEquals( $this->obj->thumb_file_url, env('GOOGLE_CLOUD_STORAGE_API_URI')."/123.png");
        \Storage::fake();
        $video = Video::create(
            $this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->image('video.jpg'),
                // 'banner_file' => UploadedFile::fake()->image('banner.jpg'),
                // 'trailer_file' => UploadedFile::fake()->image('trailer.jpg')
            ]
        );

        $this->assertEquals($video->thumb_file_url, env('GOOGLE_CLOUD_STORAGE_API_URI')."/".$video->thumb_file);
        $this->assertEquals($video->video_file_url, env('GOOGLE_CLOUD_STORAGE_API_URI')."/".$video->video_file);
        // $this->assertEquals($video->banner_file_url, env('GOOGLE_CLOUD_STORAGE_API_URI')."/".$video->banner_file);
        // $this->assertEquals($video->trailer_file_url, env('GOOGLE_CLOUD_STORAGE_API_URI')."/".$video->trailer_file);
    }
}