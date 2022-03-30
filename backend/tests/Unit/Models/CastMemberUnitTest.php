<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Traits\SerializeDateToISO8601;
use EloquentFilter\Filterable;

class CastMemberUnitTest extends TestCase
{
    use DatabaseMigrations;

    private $castMember;

    public static function setUpBeforeClass(): void
    {
        //parent::setUpBeforeClass();

    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = new CastMember();
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
            ['name', 'type'],
            $this->castMember->getFillable()
        );
    }

    public function testIncrementingAttribute()
    {
        $this->assertFalse($this->castMember->incrementing);
    }

    public function testKeyTypeAttribute()
    {
        $this->assertEquals(
            'string',
            $this->castMember->getKeyType()
        );
    }

    public function testCastsAttribute()
    {
        $this->assertEquals(
            ['id' => 'string', 'name' => 'string', 'type' => 'integer'],
            $this->castMember->getCasts()
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

        $castMemberTraits = array_keys(class_uses(CastMember::class));

        $this->assertEquals($traits, $castMemberTraits);
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at', 'updated_at', 'created_at'];

        foreach ($dates as $date) {
            $this->assertContains($date, $this->castMember->getDates());
        }

        $this->assertCount(count($dates), $this->castMember->getDates());
    }
}
