<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;
use App\Models\Translation;

class LargeDatasetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = Tag::factory()->count(5)->create();

        // Create 10k records via models (for simplicity). For 100k+, prefer the command.
        Translation::factory()->count(10000)->create()->each(function (Translation $t) use ($tags) {
            $t->tags()->attach($tags->random(rand(1, 3))->pluck('id')->all());
        });
    }
}
