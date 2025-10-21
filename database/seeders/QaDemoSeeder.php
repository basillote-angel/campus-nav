<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@navistfind.local'],
            ['name' => 'Admin', 'role' => 'admin', 'password' => Hash::make('password')]
        );

        $student = User::firstOrCreate(
            ['email' => 'student@navistfind.local'],
            ['name' => 'Student One', 'role' => 'student', 'password' => Hash::make('password')]
        );

        $staff = User::firstOrCreate(
            ['email' => 'staff@navistfind.local'],
            ['name' => 'Staff One', 'role' => 'staff', 'password' => Hash::make('password')]
        );

        $now = Carbon::now();

        $lostItems = [
            ['name' => 'Black Wallet', 'category' => 'accessories', 'location' => 'Building A Lobby'],
            ['name' => 'ID Card', 'category' => 'idOrCards', 'location' => 'Gate Entrance'],
            ['name' => 'Blue Backpack', 'category' => 'bagOrPouches', 'location' => 'Library'],
        ];
        foreach ($lostItems as $i => $li) {
            Item::updateOrCreate(
                ['name' => $li['name'], 'type' => 'lost'],
                [
                    'owner_id' => $student->id,
                    'finder_id' => null,
                    'category' => $li['category'],
                    'description' => $li['name'].' lost around '.$li['location'],
                    'type' => 'lost',
                    'location' => $li['location'],
                    'status' => 'unclaimed',
                    'lost_found_date' => $now->copy()->subDays(7 - $i),
                ]
            );
        }

        $foundItems = [
            ['name' => 'Leather Wallet', 'category' => 'accessories', 'location' => 'Admin Office'],
            ['name' => 'School ID', 'category' => 'idOrCards', 'location' => 'Cafeteria'],
            ['name' => 'Backpack', 'category' => 'bagOrPouches', 'location' => 'Gym'],
        ];
        foreach ($foundItems as $i => $fi) {
            Item::updateOrCreate(
                ['name' => $fi['name'], 'type' => 'found'],
                [
                    'owner_id' => null,
                    'finder_id' => $staff->id,
                    'category' => $fi['category'],
                    'description' => 'Found '.$fi['name'].' at '.$fi['location'],
                    'type' => 'found',
                    'location' => $fi['location'],
                    'status' => 'unclaimed',
                    'lost_found_date' => $now->copy()->subDays(5 - $i),
                ]
            );
        }
    }
}


