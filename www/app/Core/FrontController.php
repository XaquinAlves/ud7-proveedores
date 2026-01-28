<?php

namespace Com\Daw2\Core;

use Com\Daw2\Controllers\UsuariosSistemaController;
use Com\Daw2\Libraries\JWTHelper;
use Com\Daw2\Models\UsuariosSistemaModel;
use Com\Daw2\Traits\JwtTool;
use Steampixel\Route;

class FrontController
{
    private static false|array $user = false;
    public static function main(): void
    {
        if (JwtTool::requestHasToken()) {
            $token = JwtTool::getBearerToken();
            $payload = (new JWTHelper())->decodeToken($token);
            self::$user = (new UsuariosSistemaModel())->findById($payload['id_usuario']);
        }
        Route::add('/login', function () {
            $controller = new UsuariosSistemaController();
            $controller->login();
        }, 'post');

        Route::pathNotFound(
            function () {
                http_response_code(404);
            }
        );

        Route::methodNotAllowed(
            function () {
                http_response_code(405);
            }
        );

        Route::run();
    }
}
