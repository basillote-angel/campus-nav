<?php

namespace Database\Factories;

use App\Enums\LostItemStatus;
use App\Models\Category;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LostItemFactory extends Factory
{
	protected $model = LostItem::class;

	public function definition(): array
	{
		return [
			'user_id' => User::factory(),
			'category_id' => Category::factory(),
			'title' => $this->faker->sentence(3),
			'description' => $this->faker->sentence(),
			'image_path' => null,
			'location' => $this->faker->word(),
			'date_lost' => $this->faker->date(),
			'status' => LostItemStatus::LOST_REPORTED->value,
		];
	}
}



