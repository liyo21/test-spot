<?php

namespace Tests\Feature;

use App\Models\Url;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteUrlShortenerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_delete_url_found(): void
    {
        $url = Url::create([
            'original_url'  => 'https://www.google.com',
            'shortened_url' => Str::random(8)
        ]);

        $response = $this->deleteJson('/api/urls/' . $url->shortened_url);

        $response->assertStatus(200);

        $response->assertJson([
            'status' => 'OK',
            'message' => 'The shortenUrl ' . $url->shortened_url . ' deleted',
            'response' => []
        ]);
    }

    public function test_delete_url_not_found(): void
    {
        $shortenUrl = 'KVDtiv123';

        $response = $this->deleteJson('/api/urls/' . $shortenUrl);

        $response->assertStatus(404);
        $response->assertJson([
            'status'    => 'NOK',
            'message'   => 'The shortenUrl ' . $shortenUrl . ' was not found',
            'response'  => []
        ]);
    }
}
