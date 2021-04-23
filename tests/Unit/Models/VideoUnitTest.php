<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Traits\Uuid;
use App\Models\Video;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UploadFiles;

class VideoUnitTest extends TestCase
{

    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = new Video();

    }

    public function testFillableAttribute()
    {
        $this->assertEquals(
            [
                'title',
                'description',
                'year_launched',
                'opened',
                'rating',
                'duration',
                'video_file',
                'thumb_file',
                'banner_file',
                'trailer_file'
            ],
            $this->video->getFillable()
        );
    }

    public function testFileFields()
    {
        $this->assertEquals(
            [
                'video_file',
                'thumb_file',
                'banner_file',
                'trailer_file'
            ],
            $this->video::$fileFields
        );
    }

    public function testIncrementingAttribute(){
        $this->assertFalse($this->video->incrementing);
    }

    public function testKeyTypeAttribute(){
        $this->assertEquals(
            'string',
            $this->video->getKeyType()
        );
    }

    public function testCastsAttribute(){
        $this->assertEquals(
            [
                'id' => 'string',
                'title' => 'string',
                'description' => 'string',
                'year_launched' => 'integer',
                'opened' => 'boolean',
                'rating' => 'string',
                'duration' => 'integer'
            ],
            $this->video->getCasts()
        );
    }

    public function testIfUseTraits(){
        $traits = [
            SoftDeletes::class, Uuid::class, UploadFiles::class
        ];

        $videoTraits = array_keys(class_uses(Video::class));

        $this->assertEquals($traits, $videoTraits);
    }

    public function testDatesAttribute(){
        $dates = ['deleted_at','updated_at','created_at'];

        foreach($dates as $date){
            $this->assertContains($date,$this->video->getDates());
        }

        $this->assertCount(count($dates), $this->video->getDates());
    }

    public function testCategoriesMethodExists(){
        $methods = [
            'categories',
            'genres'
        ];
        $this->verifyMethodExists($methods);
    }

    protected function verifyMethodExists(array $methods){
        foreach($methods as $method){
            $this->assertTrue(method_exists(Video::class, $method));
        }
    }
}
