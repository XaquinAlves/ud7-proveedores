<?php

declare(strict_types=1);

namespace Com\Daw2\Libraries;

use Ahc\Jwt\JWT;

class JWTHelper
{
    private const SECRET = 'n)Fh/Hlz{U-i,C02=KZ=utkPOMuo0f3O>e,(4D.?ITJ';
    private const ALGO = 'HS256';
    private const EXPIRATION = 1800;
    private const LEEWAY = 10;
    public function decodeToken(string $token)
    {
        $jwt = new JWT(self::SECRET, self::ALGO, self::EXPIRATION, self::LEEWAY);
        return $jwt->decode($token);
    }

    public function getToken(array $payload)
    {
        $jwt = new JWT(self::SECRET, self::ALGO, self::EXPIRATION, self::LEEWAY);
        return $jwt->encode($payload);
    }
}
