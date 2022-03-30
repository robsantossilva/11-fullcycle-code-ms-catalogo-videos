<?php

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait TestResources
{
    protected function assertResource(TestResponse $response, JsonResource $resource)
    {
        return $response->assertJson($resource->response()->getData(true));
    }
}
