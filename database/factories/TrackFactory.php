<?php

$factory->define(App\Track::class, function (Faker\Generator $faker) {
    return [
        'type' => 0,
        'url' => '404',
        'title' => str_replace('.', '', $faker->text(24)),
        'artist' => $faker->firstName . ' ' . $faker->lastName,
        'album' => str_replace('.', '', $faker->text(24))
    ];
});
