<?php

namespace Tests\Feature;

use Mockery;
use App\Models\Url;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateUrlShortenerTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     */
    public function test_create_url_shortener(): void
    {
        $requestData = [
            'url' => 'https://www.google.com'
        ];

        // Crear el mock del modelo Url
        $shortenedUrl   = 'short.ly/example';
        $urlMock        = Mockery::mock('alias:App\Models\Url');
        $urlMock->shouldReceive('shortenUrl')->once()->with($requestData['url'])->andReturn($shortenedUrl);

        // Mock de la respuesta
        $responseData = [
            'status' => 'OK',
            'message' => 'Url Shortened almacenada correctamente',
            'response' => [
                'original_url' => $requestData['url'],
                'shortened_url' => $shortenedUrl
            ]
        ];

        $response = $this->postJson('/api/shortenUrl', $requestData);

        $response->assertStatus(200);
        $response->assertJson($responseData);

        Mockery::close();
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

        // Mock de la respuesta
        $responseData = [
            'status' => 'NOK',
            'message' => [
                'url' => [ $message ]
            ],
            'response' => []
        ];

        $response = $this->postJson('/api/shortenUrl', $requestData);

        $response->assertStatus(422);
        $response->assertJson($responseData);

    }
}
