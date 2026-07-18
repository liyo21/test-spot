<?php

namespace Tests\Feature;

use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteUrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_url_found(): void
    {
        $url = Url::create([
            'original_url' => 'https://www.google.com',
            'shortened_url' => 'GOOGLE01',
        ]);

        $response = $this->deleteJson('/api/urls/'.$url->shortened_url);

        $response->assertOk();
        $this->assertDatabaseMissing('urls', ['id' => $url->id]);
    }

    public function test_delete_url_not_found_returns_standard_error_response(): void
    {
        $response = $this->deleteJson('/api/urls/UNKNOWN1');

        $response
            ->assertNotFound()
            ->assertJsonPath('status', 'NOK')
            ->assertJsonPath('message', 'The requested shortened URL was not found.')
            ->assertJsonPath('response', []);
    }
}
