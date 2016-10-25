<?php

$factory->define(App\Profile::class, function (Faker\Generator $faker) {
    return [
        'bio' => $faker->paragraph()
    ];
});
