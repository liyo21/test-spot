<?php

namespace App\Services;

use App\Contracts\ShortCodeGenerator;
use App\Exceptions\ShortCodeGenerationException;
use App\Exceptions\UrlAlreadyShortenedException;
use App\Models\Url;
use Illuminate\Database\QueryException;

class UrlShortener
{
    private const MAX_CODE_ATTEMPTS = 5;

    public function __construct(
        private readonly UrlNormalizer $normalizer,
        private readonly ShortCodeGenerator $codeGenerator,
    ) {
    }

    public function shorten(string $url): Url
    {
        $originalUrl = $this->normalizer->normalize($url);

        if (Url::where('original_url', $originalUrl)->exists()) {
            throw new UrlAlreadyShortenedException();
        }

        for ($attempt = 0; $attempt < self::MAX_CODE_ATTEMPTS; $attempt++) {
            $shortenedUrl = $this->codeGenerator->generate();

            try {
                return Url::create([
                    'original_url' => $originalUrl,
                    'shortened_url' => $shortenedUrl,
                ]);
            } catch (QueryException $exception) {
                if (! $this->isUniqueViolation($exception)) {
                    throw $exception;
                }

                if (Url::where('original_url', $originalUrl)->exists()) {
                    throw new UrlAlreadyShortenedException(previous: $exception);
                }

                if (Url::where('shortened_url', $shortenedUrl)->exists()) {
                    continue;
                }

                throw $exception;
            }
        }

        throw new ShortCodeGenerationException('Unable to generate a unique short code.');
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return $exception->getCode() === '23000'
            || $exception->getCode() === '23505'
            || str_contains($exception->getMessage(), 'UNIQUE constraint failed');
    }
}
