<?php

namespace Tests\Feature;

use App\Contracts\ShortCodeGenerator;
use App\Models\Url;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SequenceShortCodeGenerator;
use Tests\TestCase;

class CreateUrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Url::flushEventListeners();

        parent::tearDown();
    }

    public function test_create_url_shortener_normalizes_and_persists_the_url(): void
    {
        Log::spy();

        $response = $this->postJson('/api/url', [
            'url' => ' HTTPS://Example.COM:443/?b=2&a=1#section ',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('response.original_url', 'https://example.com/?a=1&b=2#section');

        $this->assertDatabaseHas('urls', [
            'original_url' => 'https://example.com/?a=1&b=2#section',
        ]);
        Log::shouldNotHaveReceived('info');
    }

    public function test_create_invalid_url_returns_standard_error_response(): void
    {
        $response = $this->postJson('/api/url', ['url' => 'google']);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'NOK')
            ->assertJsonPath('message', 'The request contains invalid data.')
            ->assertJsonStructure(['response' => ['url']]);
    }

    public function test_create_duplicate_url_returns_conflict_after_normalization(): void
    {
        $this->postJson('/api/url', ['url' => 'https://example.com/?b=2&a=1'])->assertOk();

        $response = $this->postJson('/api/url', ['url' => 'https://EXAMPLE.com:443/?a=1&b=2']);

        $response
            ->assertConflict()
            ->assertJsonPath('status', 'NOK')
            ->assertJsonPath('message', 'The URL has already been shortened.')
            ->assertJsonPath('response', []);
        $this->assertDatabaseCount('urls', 1);
    }

    public function test_unique_violation_during_insert_returns_conflict(): void
    {
        $url = 'https://example.com/race';
        $inserted = false;

        Url::creating(function () use (&$inserted, $url): void {
            if ($inserted) {
                return;
            }

            $inserted = true;
            DB::table('urls')->insert([
                'original_url' => $url,
                'shortened_url' => 'RACE0001',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $response = $this->postJson('/api/url', ['url' => $url]);

        $response
            ->assertConflict()
            ->assertJsonPath('status', 'NOK')
            ->assertJsonPath('message', 'The URL has already been shortened.');
        $this->assertDatabaseCount('urls', 1);
    }

    public function test_short_code_collision_is_retried(): void
    {
        Url::create([
            'original_url' => 'https://example.com/existing',
            'shortened_url' => 'COLLIDE1',
        ]);
        $this->app->instance(ShortCodeGenerator::class, new SequenceShortCodeGenerator([
            'COLLIDE1',
            'UNIQUE01',
        ]));

        $response = $this->postJson('/api/url', ['url' => 'https://example.com/new']);

        $response
            ->assertOk()
            ->assertJsonPath('response.shortened_url', 'UNIQUE01');
    }

    public function test_short_code_collision_after_five_attempts_returns_problem_details(): void
    {
        Url::create([
            'original_url' => 'https://example.com/existing',
            'shortened_url' => 'COLLIDE1',
        ]);
        $this->app->instance(ShortCodeGenerator::class, new SequenceShortCodeGenerator(array_fill(0, 5, 'COLLIDE1')));

        $response = $this->postJson('/api/url', ['url' => 'https://example.com/new']);

        $response
            ->assertStatus(500)
            ->assertJsonPath('status', 'NOK')
            ->assertJsonPath('message', 'The request could not be completed.')
            ->assertJsonPath('response', []);
    }
}
