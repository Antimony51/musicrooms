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
        factory(App\User::class, 50)->create()->each(function($user) {
            $user->profile()->save(factory(App\Profile::class)->make());

            $roomCount = rand(0, 4);
            for ($i = 0; $i < $roomCount; $i++){
                $user->rooms()->save(factory(App\Room::class)->make());
            }

            $friendCount = rand(0, 8);
            $otherUsers = App\User::inRandomOrder()->take($friendCount)->get();
            foreach ($otherUsers as $otherUser){
                $user->befriend($otherUser);
                $otherUser->acceptFriendRequest($user);
            }

            $favCount = rand(0, 8);
            $tracks = App\Track::inRandomOrder()->take($favCount)->get();
            foreach ($tracks as $track){
                $user->favoriteTracks()->save($track);
            }
        });
    }
}
