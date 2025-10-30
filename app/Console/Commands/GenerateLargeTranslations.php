<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Tag;

class GenerateLargeTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-large-translations {--count=100000} {--batch=5000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a large dataset of translations with tags in efficient batches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $batchSize = (int) $this->option('batch');
        $locales = ['en', 'fr', 'es'];

        $this->info("Generating {$count} translations in batches of {$batchSize}...");

        // Ensure some tags exist
        $tagNames = ['mobile', 'desktop', 'web', 'admin', 'public'];
        $tags = collect($tagNames)->map(function ($name) {
            return Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id;
        });

        $inserted = 0;
        while ($inserted < $count) {
            $remaining = $count - $inserted;
            $take = min($batchSize, $remaining);

            $now = now();
            $translations = [];
            for ($i = 0; $i < $take; $i++) {
                $key = Str::slug(fake()->words(3, true), '.').'.'.Str::lower(Str::random(6));
                $locale = $locales[array_rand($locales)];
                $value = fake()->sentence(8);
                $context = json_encode(['platform' => $tagNames[array_rand($tagNames)]]);

                $translations[] = [
                    'key' => $key,
                    'locale' => $locale,
                    'value' => $value,
                    'context' => $context,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('translations')->insert($translations);

            // Attach tags via pivot in smaller chunks to avoid memory spikes
            $translationIds = DB::table('translations')
                ->orderByDesc('id')
                ->limit($take)
                ->pluck('id')
                ->all();

            $pivot = [];
            foreach ($translationIds as $tid) {
                $attach = $tags->shuffle()->take(rand(1, 3));
                foreach ($attach as $tagId) {
                    $pivot[] = [
                        'translation_id' => $tid,
                        'tag_id' => $tagId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Chunk pivot inserts
            foreach (array_chunk($pivot, 1000) as $chunk) {
                DB::table('translation_tag')->insert($chunk);
            }

            $inserted += $take;
            $this->info("Inserted {$inserted}/{$count}");
        }

        $this->info('Done generating large dataset.');
    }
}
