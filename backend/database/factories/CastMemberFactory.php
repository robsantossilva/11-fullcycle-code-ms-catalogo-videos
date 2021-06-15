<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CastMember;
use Faker\Generator as Faker;

$factory->define(CastMember::class, function (Faker $faker) {
    
    $arrayTypes = [CastMember::TYPE_DIRECTOR, CastMember::TYPE_ACTOR];
    
    return [
        'name' => $faker->colorName,
        'type' => $arrayTypes[array_rand($arrayTypes)]
    ];
});
