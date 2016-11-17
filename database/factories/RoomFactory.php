<?php

$factory->define(App\Room::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(16),
        'visibility' => rand(0,1) ? 'public' : 'private',
        'title' => str_replace('//', '', $faker->text(24)),
        'description' => $faker->paragraph(3, true)
    ];
});
