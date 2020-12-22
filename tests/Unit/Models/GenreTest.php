<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use PHPUnit\Framework\TestCase;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = new Genre();

    }

    public function testFillableAttribute()
    {
        $this->assertEquals(
            ['name','description','is_active'],
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
