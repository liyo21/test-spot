<?php

namespace Tests\Feature;

use App\Models\Url;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetUrlShortenedTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_url_found(): void
    {
        $originalUrl = 'https://www.google.com';

        $url = Url::create([
            'original_url'  => $originalUrl,
            'shortened_url' => Str::random(8)
        ]);

        $response = $this->getJson('/api/urls/' . $url->shortened_url);

        $response->assertStatus(200);

        $response->assertJson([
            'status' => 'OK',
            'message' => 'The shortenUrl ' . $url->shortened_url . ' was found',
            'response' => [
                'original_url' => $originalUrl
            ]
        ]);
    }

    public function test_show_url_not_found(): void
    {
        $shortenUrl = 'KVDtiv123';

        $response = $this->getJson('/api/urls/' . $shortenUrl);

        $response->assertStatus(404);

        $response->assertJson([
            'status'    => 'NOK',
            'message'   => 'The shortenUrl ' . $shortenUrl . ' was not found',
            'response'  => []
        ]);
    }
}
