<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {

    $arrayIsActive = [true, false];

    return [
        'name'=> $faker->colorName,
        'description'=> rand(1,10) % 2 == 0 ? $faker->sentence() : null,
        'is_active'=> $arrayIsActive[array_rand($arrayIsActive)]
    ];
});
