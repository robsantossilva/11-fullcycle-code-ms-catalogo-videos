<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Traits\Uuid;
use App\Models\Video;
use Illuminate\Database\Eloquent\SoftDeletes;

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
                'duration'
            ],
            $this->genre->getFillable()
        );
    }

    public function testIncrementingAttribute(){
        $this->assertFalse($this->genre->incrementing);
    }

    public function testKeyTypeAttribute(){
        $this->assertEquals(
            'string',
            $this->genre->getKeyType()
        );
    }

    public function testCastsAttribute(){
        $this->assertEquals(
            ['id'=>'string','is_active'=>'boolean'],
            $this->genre->getCasts()
        );
    }

    public function testIfUseTraits(){
        $traits = [
            SoftDeletes::class, Uuid::class
        ];

        $genreTraits = array_keys(class_uses(Genre::class));

        $this->assertEquals($traits, $genreTraits);
    }

    public function testDatesAttribute(){
        $dates = ['deleted_at','updated_at','created_at'];

        foreach($dates as $date){
            $this->assertContains($date,$this->genre->getDates());
        }

        $this->assertCount(count($dates), $this->genre->getDates());
    }
}
