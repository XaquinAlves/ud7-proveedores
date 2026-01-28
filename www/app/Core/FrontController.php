<?php

namespace Com\Daw2\Core;

use Com\Daw2\Controllers\ProveedorController;
use Com\Daw2\Controllers\UsuarioSistemaController;
use Com\Daw2\Libraries\JWTHelper;
use Com\Daw2\Models\UsuarioSistemaModel;
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
            self::$user = (new UsuarioSistemaModel())->findById($payload['id_usuario']);
        }
        Route::add('/login', function () {
            $controller = new UsuarioSistemaController();
            $controller->login();
        }, 'post');

        Route::add('/proveedor', function () {
            if (str_contains('r', self::$user['permisos']['proveedor'])) {
                $controller = new ProveedorController();
            } else {
                http_response_code(403);
            }
        }, 'get');

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
