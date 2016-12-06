<?php

$factory->define(App\User::class, function (Faker\Generator $faker) {
    $name = $faker->userName;
    $data = [
        'name' => $name,
        'email' => $name . '@example.com',
        'password' => bcrypt('secret'),
        'remember_token' => str_random(10),
    ];

    if (config('auth.passwords.users.use_security_questions')){
        $numSecurityQuestions = config('auth.passwords.users.num_security_questions');
        $questions = [];
        $answers = [];
        for ($i=0; $i < $numSecurityQuestions; $i++) {
            $questions[$i] = $faker->sentence();
            $answers[$i] = bcrypt('secret');
        }
        $data = array_merge($data, [
            'questions' => $questions,
            'answers' => $answers,
        ]);
    }
    return $data;
});
