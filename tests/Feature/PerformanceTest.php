<?php

namespace Tests\Feature;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoints_latency_targets(): void
    {
        // Prepare some data
        Translation::factory()->count(2000)->create(['locale' => 'en']);

        // Export under 500ms
        $start = microtime(true);
        $this->getJson('/api/translations/export?locale=en')->assertOk();
        $elapsedMs = (microtime(true) - $start) * 1000;
        $this->assertLessThan(500, $elapsedMs, 'Export exceeded 500ms');
    }
}


