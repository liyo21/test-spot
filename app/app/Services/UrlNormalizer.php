<?php

namespace App\Services;

use InvalidArgumentException;

class UrlNormalizer
{
    public function normalize(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException('The URL cannot be normalized.');
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $authority = '';

        if (isset($parts['user'])) {
            $authority .= $parts['user'];
            if (isset($parts['pass'])) {
                $authority .= ':'.$parts['pass'];
            }
            $authority .= '@';
        }

        $authority .= $host;

        $port = $parts['port'] ?? null;
        if ($port !== null && ! (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443))) {
            $authority .= ':'.$port;
        }

        $path = $parts['path'] ?? '/';
        if ($path === '') {
            $path = '/';
        }

        $normalized = $scheme.'://'.$authority.$path;

        if (array_key_exists('query', $parts) && $parts['query'] !== '') {
            $normalized .= '?'.$this->normalizeQuery($parts['query']);
        }

        if (array_key_exists('fragment', $parts)) {
            $normalized .= '#'.$parts['fragment'];
        }

        return $normalized;
    }

    private function normalizeQuery(string $query): string
    {
        $pairs = [];

        foreach (explode('&', $query) as $position => $segment) {
            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            $pairs[] = [
                'key' => urldecode($key),
                'value' => $value === null ? null : urldecode($value),
                'position' => $position,
            ];
        }

        usort($pairs, static function (array $left, array $right): int {
            return [$left['key'], $left['value'] ?? '', $left['position']]
                <=> [$right['key'], $right['value'] ?? '', $right['position']];
        });

        return implode('&', array_map(static function (array $pair): string {
            $key = rawurlencode($pair['key']);

            return $pair['value'] === null
                ? $key
                : $key.'='.rawurlencode($pair['value']);
        }, $pairs));
    }
}
