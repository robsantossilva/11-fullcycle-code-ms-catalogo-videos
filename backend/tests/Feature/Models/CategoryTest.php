<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use \Ramsey\Uuid\Uuid as RamseyUuid;

class CategoryTest extends TestCase
{

    use DatabaseMigrations;

    public function testList()
    {
        factory(Category::class,1)->create();

        $categories = Category::all();
        $this->assertCount(1,$categories);

        $category = $categories->first();
        $categoryKey = array_keys($category->getAttributes());

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
            $categoryKey
        );
    }

    public function testCreate()
    {
        $category = Category::create([
            'name'=>'test1'
        ]);
        $category->refresh();
        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        //////////////////////////////////////////////////////////
        $category = Category::create([
            'name'=>'test2',
            'description'=>null
        ]);
        $category->refresh();
        $this->assertNull($category->description);
        //////////////////////////////////////////////////////////
        $category = Category::create([
            'name'=>'test2',
            'description'=>'test2_description'
        ]);
        $category->refresh();
        $this->assertEquals('test2_description', $category->description);
        //////////////////////////////////////////////////////////
        $category = Category::create([
            'name'=>'test2',
            'is_active'=>false
        ]);
        $category->refresh();
        $this->assertFalse($category->is_active);
        //////////////////////////////////////////////////////////
        $category = Category::create([
            'name'=>'test2',
            'is_active'=>true
        ]);
        $category->refresh();
        $this->assertTrue($category->is_active);
        //////////////////////////////////////////////////////////
        $uuid = RamseyUuid::fromString($category->id);
        $this->assertEquals(RamseyUuid::UUID_TYPE_RANDOM,$uuid->getVersion());
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description'=>'test_description',
            'is_active'=>false
        ]);
        $category->update([
            'name'=>'test_name_updated',
            'description'=>'test_name_description',
            'is_active'=>true
        ]);
        $this->assertEquals('test_name_updated', $category->name);
        $this->assertEquals('test_name_description', $category->description);
        $this->assertTrue($category->is_active);
    }
}