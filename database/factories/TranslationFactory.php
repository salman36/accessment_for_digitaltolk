<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locales = ['en', 'fr', 'es'];
        return [
            'key' => Str::slug($this->faker->unique()->words(3, true), '.'),
            'locale' => $this->faker->randomElement($locales),
            'value' => $this->faker->sentence(8),
            'context' => [
                'platform' => $this->faker->randomElement(['mobile', 'desktop', 'web']),
            ],
        ];
    }
}
