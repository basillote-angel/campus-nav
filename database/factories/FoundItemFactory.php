<?php

namespace Database\Factories;

use App\Enums\FoundItemStatus;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FoundItemFactory extends Factory
{
	protected $model = FoundItem::class;

	public function definition(): array
	{
		return [
			'user_id' => User::factory(),
			'category_id' => Category::factory(),
			'title' => $this->faker->sentence(3),
			'description' => $this->faker->sentence(),
			'image_path' => null,
			'location' => $this->faker->word(),
			'date_found' => $this->faker->date(),
			'status' => FoundItemStatus::FOUND_UNCLAIMED->value,
		];
	}
}



