<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): string
    {
        $user = User::factory()->create();
        $resp = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        if ($resp->status() !== 200) {
            // If login fails due to hashing, login via register
            $resp = $this->postJson('/api/auth/register', [
                'name' => 'Test',
                'email' => 't@example.com',
                'password' => 'password123',
            ]);
        }
        return $resp->json('token');
    }

    public function test_create_and_search_translation(): void
    {
        $token = $this->authenticate();

        $create = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/translations', [
                'key' => 'greeting.hello',
                'locale' => 'en',
                'value' => 'Hello',
                'tags' => ['web'],
            ]);
        $create->assertStatus(201);

        $search = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/translations?locale=en&key=greeting');
        $search->assertOk()->assertJsonFragment(['value' => 'Hello']);
    }

    public function test_export_endpoint_is_fast(): void
    {
        Translation::factory()->count(2000)->create(['locale' => 'en']);
        $start = microtime(true);
        $res = $this->getJson('/api/translations/export?locale=en');
        $res->assertOk();
        $elapsedMs = (microtime(true) - $start) * 1000;
        $this->assertLessThan(500, $elapsedMs, 'Export exceeded 500ms');
    }
}


