<?php

namespace Tests\Feature;

use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Proves the test harness itself: fresh in-memory DB, migrations,
 * seeders, auth, and a real HTTP round-trip. If this file fails,
 * fix the harness before trusting anything else.
 */
class HarnessSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_migrates_and_seeds(): void
    {
        $this->assertGreaterThan(0, Button::count(), 'ButtonSeeder should populate buttons');
    }

    public function test_home_page_responds(): void
    {
        $this->get('/')->assertSuccessful();
    }

    public function test_authenticated_studio_loads(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/studio/edit')
            ->assertSuccessful()
            ->assertSee('Appearance');
    }
}
