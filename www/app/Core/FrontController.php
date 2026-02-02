<?php

namespace Com\Daw2\Core;

use Com\Daw2\Controllers\ProveedorController;
use Com\Daw2\Controllers\UsuarioSistemaController;
use Com\Daw2\Libraries\JWTHelper;
use Com\Daw2\Libraries\Respuesta;
use Com\Daw2\Traits\JwtTool;
use Steampixel\Route;
use Ahc\Jwt\JWTException;

class FrontController
{
    public static false|array $user = false;

    public static function main(): void
    {

        try {
            if (JwtTool::requestHasToken()) {
                try {
                    $token = JwtTool::getBearerToken();
                    self::$user = JWTHelper::decodeToken($token);
                    self::$user['permisos'] = UsuarioSistemaController::getPermisos((int)self::$user['id_rol']);
                } catch (JWTException $e) {
                    header('HTTP/1.1 403 Forbidden, ' . $e->getMessage(), true, 403);
                }
            }
            Route::add('/login', function () {
                (new UsuarioSistemaController())->login();
            }, 'post');
            Route::add('/proveedor', function () {
                if (in_array('proveedor.get', self::$user['permisos'])) {
                    (new ProveedorController())->getProveedorByFilters();
                } else {
                    http_response_code(403);
                }
            }, 'get');
            Route::add('/proveedor/([A-Z][0-9]{7}[A-Z])', function ($cif) {
                if (in_array('proveedor.get', self::$user['permisos'])) {
                    (new ProveedorController())->getProveedorByCif($cif);
                } else {
                    http_response_code(403);
                }
            }, 'get');
            Route::add('/proveedor', function () {
                if (in_array('proveedor.post', self::$user['permisos'])) {
                    (new ProveedorController())->postProveedor();
                } else {
                    http_response_code(403);
                }
            }, 'post');
            Route::add('/proveedor/([A-Z][0-9]{7}[A-Z])', function ($cif) {
                if (in_array('proveedor.delete', self::$user['permisos'])) {
                    (new ProveedorController())->deleteProveedor($cif);
                } else {
                    http_response_code(403);
                }
            }, 'delete');
            Route::add('/proveedor/([A-Z][0-9]{7}[A-Z])', function ($cif) {
                if (in_array('proveedor.patch', self::$user['permisos'])) {
                    (new ProveedorController())->patchProveedor($cif);
                } else {
                    http_response_code(403);
                }
            }, 'patch');
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
        } catch (JWTException $e) {
            $respuesta = new Respuesta(403);
            $respuesta->setData(['mensaje' => $e->getMessage()]);
            (new View('FrontController'))->show('json.view.php', ['respuesta' => $respuesta]);
        }
    }
}
