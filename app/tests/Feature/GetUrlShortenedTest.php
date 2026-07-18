<?php

namespace Tests\Feature;

use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUrlShortenedTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_url_found(): void
    {
        $url = Url::create([
            'original_url' => 'https://www.google.com',
            'shortened_url' => 'GOOGLE01',
        ]);

        $response = $this->getJson('/api/urls/'.$url->shortened_url);

        $response
            ->assertOk()
            ->assertJsonPath('response.original_url', 'https://www.google.com');
    }

    public function test_show_url_not_found_returns_problem_details(): void
    {
        $response = $this->getJson('/api/urls/UNKNOWN1');

        $response
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('type', 'urn:test-spot:url-not-found')
            ->assertJsonPath('status', 404);
    }
}
