<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionClass;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class BasicCrudControllerTest extends TestCase
{

  /** @var CategoryControllerStub $controller */
  private $controller;
  
  protected function setUp(): void
  {
    parent::setUp();
    CategoryStub::dropTable();
    CategoryStub::createTable();
    $this->controller = new CategoryControllerStub();
  }

  protected function tearDown(): void
  {
    CategoryStub::dropTable();
    parent::tearDown();
  }

  public function testIndex()
  {
    /** @var CategoryStub $category */
    $category = CategoryStub::create([
      'name'=>'test_name',
      'description'=>'test_description',
      'is_active'=> false
    ]);
    $category->refresh();
    $result = $this->controller->index($resquest = new Request)->toArray();

    $this->assertEquals([$category->toArray()],$result);
  }

  // /**
  //  * @expectedException \Illuminate\Validation\ValidationException
  //  */
  public function testInvalidationDataInStore()
  {
    $this->expectException(ValidationException::class);

    /** @var Request $request */
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('all')
      ->once()
      ->andReturn(['name'=>'']);

    $this->controller->store($request);
  }

  public function testStore()
  {
    /** @var Request $request */
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('all')
      ->once()
      ->andReturn(['name'=>'test_name','description'=>'test_description']);

    $obj = $this->controller->store($request);
    $this->assertEquals(
      CategoryStub::find($obj->id)->toArray(),
      $obj->toArray()
    );
  }

  public function testIfFindOrFailFetchModel()
  {
    /** @var CategoryStub $category */
    $category = CategoryStub::create([
      'name'=>'test_name',
      'description'=>'test_description'
    ]);

    $reflectionClass = new ReflectionClass(BasicCrudController::class);
    $reflectionMethod = $reflectionClass->getMethod('findOrFail');
    $reflectionMethod->setAccessible(true);

    $result = $reflectionMethod->invokeArgs($this->controller, [$category->id]);
    $this->assertInstanceOf(CategoryStub::class, $result);

  }

  public function testIfFindOrFailThrowExceptionWhenIdInvalid()
  {

    $this->expectException(ModelNotFoundException::class);

    $reflectionClass = new ReflectionClass(BasicCrudController::class);
    $reflectionMethod = $reflectionClass->getMethod('findOrFail');
    $reflectionMethod->setAccessible(true);

    $result = $reflectionMethod->invokeArgs($this->controller, [0]);
    $this->assertInstanceOf(CategoryStub::class, $result);

  }

  public function testShow()
  {
    $obj = CategoryStub::create([
      'name'=>'test_name',
      'description'=>'test_description'
    ]);
    $obj->refresh();

    $this->assertEquals($obj->toArray(), $this->controller->show($obj->id)->toArray());
  }

  public function testUpdate()
  {

    $obj = CategoryStub::create([
      'name'=>'test_name',
      'description'=>'test_description'
    ]);
    $obj->refresh();

    $data = ['name'=>'test_name_update','description'=>'test_description_update'];

    /** @var Request $request */
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('all')
      ->once()
      ->andReturn($data);

    $newObj = $this->controller->update($request, $obj->id);

    $this->assertEquals($newObj->toArray(), CategoryStub::find(1)->toArray());
  }

  public function testDestroy()
  {
    $obj = CategoryStub::create([
      'name'=>'test_name',
      'description'=>'test_description'
    ]);

    /** @var Response $response */
    $response = $this->controller->destroy($obj->id);

    $this->assertInstanceOf(Response::class, $response);
    $this->assertEquals($response->getStatusCode(), 204);

    $this->createTestResponse($response)
          ->assertStatus(204);
  }
}
