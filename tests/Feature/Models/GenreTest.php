<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use \Ramsey\Uuid\Uuid as RamseyUuid;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class,1)->create();

        $genres = Genre::all();
        $this->assertCount(1,$genres);

        $genre = $genres->first();
        $genreKey = array_keys($genre->getAttributes());

        $this->assertEqualsCanonicalizing(
            [
                "id",
                "name",
                "description",
                "is_active",
                "deleted_at",
                "created_at",
                "updated_at"
            ],
            $genreKey
        );
    }

    public function testCreate()
    {
        $genre = Genre::create([
            'name'=>'test1'
        ]);
        $genre->refresh();
        $this->assertEquals('test1', $genre->name);
        $this->assertNull($genre->description);
        $this->assertTrue($genre->is_active);
        //////////////////////////////////////////////////////////
        $genre = Genre::create([
            'name'=>'test2',
            'description'=>null
        ]);
        $genre->refresh();
        $this->assertNull($genre->description);
        //////////////////////////////////////////////////////////
        $genre = Genre::create([
            'name'=>'test2',
            'description'=>'test2_description'
        ]);
        $genre->refresh();
        $this->assertEquals('test2_description', $genre->description);
        //////////////////////////////////////////////////////////
        $genre = Genre::create([
            'name'=>'test2',
            'is_active'=>false
        ]);
        $genre->refresh();
        $this->assertFalse($genre->is_active);
        //////////////////////////////////////////////////////////
        $genre = Genre::create([
            'name'=>'test2',
            'is_active'=>true
        ]);
        $genre->refresh();
        $this->assertTrue($genre->is_active);
        //////////////////////////////////////////////////////////
        $uuid = RamseyUuid::fromString($genre->id);
        $this->assertEquals(RamseyUuid::UUID_TYPE_RANDOM,$uuid->getVersion());
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'description'=>'test_description',
            'is_active'=>false
        ])->first();
        $genre->update([
            'name'=>'test_name_updated',
            'description'=>'test_name_description',
            'is_active'=>true
        ]);
        $this->assertEquals('test_name_updated', $genre->name);
        $this->assertEquals('test_name_description', $genre->description);
        $this->assertTrue($genre->is_active);
    }
}
