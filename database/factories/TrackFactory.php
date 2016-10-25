<?php

$factory->define(App\Track::class, function (Faker\Generator $faker) {
    return [
        'type' => 'file',
        'url' => '404',
        'title' => str_replace('.', '', $faker->text(24)),
        'artist' => $faker->firstName . ' ' . $faker->lastName,
        'album' => str_replace('.', '', $faker->text(24)),
        'duration' => mt_rand(1*60, 6*60)
    ];
});
