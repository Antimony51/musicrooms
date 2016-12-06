<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new App\User();
        $admin->name = 'admin';
        $admin->email = 'admin@example.com';
        $admin->password = bcrypt('secret');
        $admin->remember_token = str_random(10);
        $admin->admin = true;

        if (config('auth.passwords.users.use_security_questions')){
            $numSecurityQuestions = config('auth.passwords.users.num_security_questions');
            $questions = [];
            $answers = [];
            for ($i=0; $i < $numSecurityQuestions; $i++) {
                $questions[$i] = "Change me!";
                $answers[$i] = bcrypt('secret');
            }
            $admin->questions = $questions;
            $admin->answers = $answers;
        }

        $admin->save();

        $admin->profile()->save(factory(App\Profile::class)->make());

        factory(App\User::class, 20)->create()->each(function($user) {
            $user->profile()->save(factory(App\Profile::class)->make());

            // $favCount = rand(0, 8);
            // $tracks = App\Track::inRandomOrder()->take($favCount)->get();
            // foreach ($tracks as $track){
            //     $user->favoriteTracks()->attach($track->id);
            // }
        });

        foreach (App\User::get() as $user) {
            $friendCount = rand(0, 4);
            $otherUsers = App\User::inRandomOrder()->take($friendCount)->get();
            foreach ($otherUsers as $otherUser){
                $user->befriend($otherUser);
                $otherUser->acceptFriendRequest($user);
            }
        }
    }
}
