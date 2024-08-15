<?php

namespace Tests\Feature;

use Mockery;
use App\Models\Url;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateUrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_url_shortener(): void
    {
        $requestData = [
            'url' => 'https://www.google.com'
        ];

        $response = $this->postJson('/api/url', $requestData);

        $response->assertStatus(200);
    }

    public function test_create_invalid_url_shortener(): void
    {
        $requestData = [
            'url' => 'google'
        ];

        $message = "";
        if (isset($requestData['url']) && $requestData['url'] != '') {
            $message = 'Por favor, ingresa una URL válida.';
        } else {
            $message = 'El campo URL es obligatorio.';
        }

        $responseData = [
            'status' => 'NOK',
            'message' => [
                'url' => [ $message ]
            ],
            'response' => []
        ];

        $response = $this->postJson('/api/url', $requestData);

        $response->assertStatus(422);
        $response->assertJson($responseData);
    }

}
