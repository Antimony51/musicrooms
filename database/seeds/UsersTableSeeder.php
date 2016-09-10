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
            $roomCount = rand(0, 4);
            for ($i = 0; $i < $roomCount; $i++){
                $user->rooms()->save(factory(App\Room::class)->make());
            }
        });
    }
}
