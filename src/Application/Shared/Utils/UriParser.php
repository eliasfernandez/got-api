<?php

namespace App\Application\Shared\Utils;

class UriParser
{
    public function getIdFromUri($uri): ?int
    {
        if (!is_string($uri)) {
            throw new UriParserException($uri);
        }

        if (!str_contains($uri, '/')) {
            return is_numeric($uri) ? intval($uri) : null;
        }

        $slug = substr($uri, strrpos($uri, '/') + 1);

        return is_numeric($slug) ? intval($slug) : null;
    }
}
