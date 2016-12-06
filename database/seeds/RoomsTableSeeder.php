<?php

use Illuminate\Database\Seeder;

class RoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Room::class, 20)->create()->each(function($room) {
            $room->owner = App\User::inRandomOrder()->first();

            foreach (App\User::inRandomOrder()->take(rand(0, 5))->get() as $user) {
                $user->savedRooms()->attach($room->id);
            }
        });
    }
}
