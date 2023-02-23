<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use PHPUnit\Framework\TestCase;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\SerializeDateToISO8601;
use EloquentFilter\Filterable;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;

class GenreUnitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = new Genre();
    }

    public function testFillableAttribute()
    {
        $this->assertEquals(
            ['name', 'description', 'is_active'],
            $this->genre->getFillable()
        );
    }

    public function testIncrementingAttribute()
    {
        $this->assertFalse($this->genre->incrementing);
    }

    public function testKeyTypeAttribute()
    {
        $this->assertEquals(
            'string',
            $this->genre->getKeyType()
        );
    }

    public function testCastsAttribute()
    {
        $this->assertEquals(
            ['id' => 'string', 'is_active' => 'boolean'],
            $this->genre->getCasts()
        );
    }

    public function testIfUseTraits()
    {
        $traits = [
            SoftDeletes::class,
            Uuid::class,
            SerializeDateToISO8601::class,
            HasBelongsToManyEvents::class,
            Filterable::class
        ];

        $genreTraits = array_keys(class_uses(Genre::class));

        $this->assertEquals($traits, $genreTraits);
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'updated_at', 'created_at'];

        foreach ($dates as $date) {
            $this->assertContains($date, $this->genre->getDates());
        }

        $this->assertCount(count($dates), $this->genre->getDates());
    }

    public function testGenresMethodExists()
    {
        $methods = [
            'categories'
        ];
        $this->verifyMethodExists($methods);
    }

    protected function verifyMethodExists(array $methods)
    {
        foreach ($methods as $method) {
            $this->assertTrue(method_exists(Genre::class, $method));
        }
    }
}
