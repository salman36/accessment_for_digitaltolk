<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(private readonly TranslationService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['locale', 'key', 'content', 'tags']);
        $perPage = (int) ($request->query('per_page', 50));
        $results = $this->service->search($filters, $perPage);
        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:10'],
            'value' => ['required', 'string'],
            'context' => ['nullable', 'array'],
            'tags' => ['array'],
        ]);

        $translation = $this->service->create($validated);
        return response()->json($translation->load('tags'), 201);
    }

    public function show(Translation $translation)
    {
        return response()->json($translation->load('tags'));
    }

    public function update(Request $request, Translation $translation)
    {
        $validated = $request->validate([
            'key' => ['sometimes', 'string', 'max:255'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'value' => ['sometimes', 'string'],
            'context' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
        ]);

        $updated = $this->service->update($translation, $validated);
        return response()->json($updated->load('tags'));
    }

    public function destroy(Translation $translation)
    {
        $translation->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'max:10'],
            'tags' => ['nullable', 'string'],
        ]);

        $tags = $validated['tags'] ? explode(',', $validated['tags']) : null;
        $data = $this->service->export($validated['locale'], $tags);

        // Always updated: set no-store; add ETag for conditional requests/CDN revalidation
        $etag = sha1(json_encode([$validated['locale'], $tags, max([now()->timestamp])]) . '|' . (string) (array_key_last($data) ?? '')); // lightweight heuristic

        $response = response()->json($data);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('ETag', $etag);
        return $response;
    }
}


