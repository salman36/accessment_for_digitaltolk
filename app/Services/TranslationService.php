<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class TranslationService
{
    public function create(array $data): Translation
    {
        $tags = Arr::pull($data, 'tags', []);
        $translation = Translation::create($data);
        $this->syncTags($translation, $tags);
        return $translation;
    }

    public function update(Translation $translation, array $data): Translation
    {
        $tags = Arr::pull($data, 'tags', null);
        $translation->update($data);
        if ($tags !== null) {
            $this->syncTags($translation, $tags);
        }
        return $translation;
    }

    public function search(array $filters, int $perPage = 50): LengthAwarePaginator
    {
        $query = Translation::query();

        if (!empty($filters['locale'])) {
            $query->where('locale', $filters['locale']);
        }
        if (!empty($filters['key'])) {
            $query->where('key', 'like', "%{$filters['key']}%");
        }
        if (!empty($filters['content'])) {
            $term = $filters['content'];
            try {
                $driver = config('database.connections.' . config('database.default') . '.driver');
                if ($driver === 'mysql') {
                    $query->whereFullText('value', $term);
                } else {
                    $query->where('value', 'like', "%{$term}%");
                }
            } catch (\Throwable $e) {
                // Fallback for SQLite / others
                $query->where('value', 'like', "%{$term}%");
            }
        }
        if (!empty($filters['tags'])) {
            $tagSlugs = is_array($filters['tags']) ? $filters['tags'] : explode(',', (string) $filters['tags']);
            $query->whereHas('tags', function (Builder $q) use ($tagSlugs) {
                $q->whereIn('slug', $tagSlugs);
            });
        }

        return $query->with('tags:id,slug,name')
            ->orderBy('key')
            ->paginate($perPage);
    }

    public function export(string $locale, ?array $tagSlugs = null): array
    {
        $query = Translation::query()->where('locale', $locale);
        if ($tagSlugs && count($tagSlugs)) {
            $query->whereHas('tags', function (Builder $q) use ($tagSlugs) {
                $q->whereIn('slug', $tagSlugs);
            });
        }

        $pairs = [];
        $query->select(['key', 'value'])
            ->orderBy('key')
            ->chunk(2000, function ($chunk) use (&$pairs) {
                foreach ($chunk as $row) {
                    $pairs[$row->key] = $row->value;
                }
            });

        return $pairs;
    }

    private function syncTags(Translation $translation, array $tags): void
    {
        if (empty($tags)) {
            $translation->tags()->sync([]);
            return;
        }
        $ids = collect($tags)->map(function ($tag) {
            $name = is_array($tag) ? ($tag['name'] ?? $tag['slug'] ?? (string) $tag) : (string) $tag;
            $slug = str($name)->slug();
            return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
        })->all();
        $translation->tags()->sync($ids);
    }
}


