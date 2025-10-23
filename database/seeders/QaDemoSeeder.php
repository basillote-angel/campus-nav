<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@navistfind.com'],
            ['name' => 'Admin', 'role' => 'admin', 'password' => Hash::make('password')]
        );

        $student = User::firstOrCreate(
            ['email' => 'student@navistfind.com'],
            ['name' => 'Student One', 'role' => 'student', 'password' => Hash::make('password')]
        );

        $staff = User::firstOrCreate(
            ['email' => 'staff@navistfind.com'],
            ['name' => 'Staff One', 'role' => 'staff', 'password' => Hash::make('password')]
        );

        $now = Carbon::now();

        $lostItems = [
            ['title' => 'Black Wallet', 'category_id' => 1, 'location' => 'Building A Lobby'],
            ['title' => 'ID Card', 'category_id' => 1, 'location' => 'Gate Entrance'],
            ['title' => 'Blue Backpack', 'category_id' => 1, 'location' => 'Library'],
        ];
        foreach ($lostItems as $i => $li) {
            LostItem::updateOrCreate(
                ['title' => $li['title']],
                [
                    'user_id' => $student->id,
                    'category_id' => $li['category_id'],
                    'description' => $li['title'].' lost around '.$li['location'],
                    'location' => $li['location'],
                    'status' => 'open',
                    'date_lost' => $now->copy()->subDays(7 - $i),
                ]
            );
        }

        $foundItems = [
            ['title' => 'Leather Wallet', 'category_id' => 1, 'location' => 'Admin Office'],
            ['title' => 'School ID', 'category_id' => 1, 'location' => 'Cafeteria'],
            ['title' => 'Backpack', 'category_id' => 1, 'location' => 'Gym'],
        ];
        foreach ($foundItems as $i => $fi) {
            FoundItem::updateOrCreate(
                ['title' => $fi['title']],
                [
                    'user_id' => $staff->id,
                    'category_id' => $fi['category_id'],
                    'description' => 'Found '.$fi['title'].' at '.$fi['location'],
                    'location' => $fi['location'],
                    'status' => 'unclaimed',
                    'date_found' => $now->copy()->subDays(5 - $i),
                ]
            );
        }
    }
}


