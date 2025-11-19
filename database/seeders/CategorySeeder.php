<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's canonical item categories.
     *
     * This keeps backend IDs in sync with the Flutter enum mapping
     * defined in `category_id_mapping.dart` (IDs 1..9).
     */
    public function run(): void
    {
        $now = now();

        $categories = [
            ['name' => 'Electronics',     'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Documents',       'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Accessories',     'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'IDs & Cards',     'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Clothing',        'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bag & Pouches',   'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Personal Items',  'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'School Supplies', 'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Others',          'description' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Upsert by name so seeding is idempotent even if IDs differ per environment
        Category::query()->upsert(
            $categories,
            ['name'],
            ['description', 'updated_at']
        );
    }
}


