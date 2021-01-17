<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use \Ramsey\Uuid\Uuid as RamseyUuid;

class CategoryTest extends TestCase
{

    use DatabaseMigrations;

    public function testList()
    {
        factory(CastMember::class,1)->create();

        $castMembers = CastMember::all();
        $this->assertCount(1,$castMembers);

        $castMember = $castMembers->first();
        $castMemberKey = array_keys($castMember->getAttributes());

        $this->assertEqualsCanonicalizing(
            [
                "id",
                "name",
                "type",
                "deleted_at",
                "created_at",
                "updated_at"
            ],
            $castMemberKey
        );
    }

    public function testCreate()
    {
        $castMember = CastMember::create([
            'name'=>'test1'
        ]);
        $castMember->refresh();
        $this->assertEquals('test1', $castMember->name);
        $this->assertEquals($castMember->type, 0);

        //////////////////////////////////////////////////////////
        $castMember = CastMember::create([
            'name'=>'test2',
            'type'=>1
        ]);
        $castMember->refresh();
        $this->assertEquals($castMember->type, 1);
        //////////////////////////////////////////////////////////
        $castMember = CastMember::create([
            'name'=>'test2',
            'type'=>2
        ]);
        $castMember->refresh();
        $this->assertEquals(2, $castMember->type);
        //////////////////////////////////////////////////////////
        $uuid = RamseyUuid::fromString($castMember->id);
        $this->assertEquals(RamseyUuid::UUID_TYPE_RANDOM,$uuid->getVersion());
    }

    public function testUpdate()
    {
        $castMember = factory(CastMember::class)->create([
            'name'=>'test_name',
            'type'=>1
        ]);
        $castMember->update([
            'name'=>'test_name_updated',
            'type'=>2
        ]);
        $this->assertEquals('test_name_updated', $castMember->name);
        $this->assertEquals(2, $castMember->type);
    }
}