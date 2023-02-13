<?php

namespace App\Tests\Utils;

use Symfony\Component\HttpFoundation\Request;

class RequestHelper {
    public static function createRequest(string $uri, string $method, string $content, array $parameters = []): Request {
        return Request::create($uri, $method, $parameters, [], [], [], $content);
    }
}
