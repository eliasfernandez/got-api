<?php

namespace App\Factory;

trait DtoFactoryTrait
{
    public function getIdFromUri(string $uri): ?int
    {
        if (!str_contains($uri, '/')) {
            return is_numeric($uri) ? intval($uri) : null;
        }

        $slug = substr($uri, strrpos($uri, '/') + 1);

        return is_numeric($slug) ? intval($slug) : null;
    }
}