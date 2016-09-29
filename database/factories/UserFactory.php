<?php

$factory->define(App\User::class, function (Faker\Generator $faker) {
    $name = $faker->userName;
    return [
        'name' => $name,
        'email' => $name . '@example.com',
        'password' => bcrypt('secret'),
        'remember_token' => str_random(10)
    ];
});
