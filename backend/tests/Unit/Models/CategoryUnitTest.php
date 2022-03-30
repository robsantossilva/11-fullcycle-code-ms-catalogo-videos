<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Traits\SerializeDateToISO8601;
use EloquentFilter\Filterable;

class CategoryUnitTest extends TestCase
{
    use DatabaseMigrations;

    private $category;

    public static function setUpBeforeClass(): void
    {
        //parent::setUpBeforeClass();

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new Category();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testFillableAttribute()
    {
        $this->assertEquals(
            ['name', 'description', 'is_active'],
            $this->category->getFillable()
        );
    }

    public function testIncrementingAttribute()
    {
        $this->assertFalse($this->category->incrementing);
    }

    public function testKeyTypeAttribute()
    {
        $this->assertEquals(
            'string',
            $this->category->getKeyType()
        );
    }

    public function testCastsAttribute()
    {
        $this->assertEquals(
            ['id' => 'string', 'is_active' => 'boolean'],
            $this->category->getCasts()
        );
    }

    public function testIfUseTraits()
    {
        $traits = [
            SoftDeletes::class,
            Uuid::class,
            SerializeDateToISO8601::class,
            Filterable::class

        ];

        $categoryTraits = array_keys(class_uses(Category::class));

        $this->assertEquals($traits, $categoryTraits);
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'updated_at', 'created_at'];

        foreach ($dates as $date) {
            $this->assertContains($date, $this->category->getDates());
        }

        $this->assertCount(count($dates), $this->category->getDates());
    }

    public function testGenresMethodExists()
    {
        $methods = [
            'genres'
        ];
        $this->verifyMethodExists($methods);
    }

    protected function verifyMethodExists(array $methods)
    {
        foreach ($methods as $method) {
            $this->assertTrue(method_exists(Category::class, $method));
        }
    }
}
