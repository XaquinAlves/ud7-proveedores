<?php

declare(strict_types=1);

namespace Com\Daw2\Controllers;

use Com\Daw2\Core\BaseController;
use Com\Daw2\Core\FrontController;
use Com\Daw2\Libraries\JWTHelper;
use Com\Daw2\Libraries\Respuesta;
use Com\Daw2\Models\UsuarioSistemaModel;
use Com\Daw2\Traits\BaseRestController;

class UsuarioSistemaController extends BaseController
{
    use BaseRestController;

    private const ID_ADMIN = 1;
    private const ID_AUDITOR = 2;
    private const ID_FACTURACION = 3;

    public function login(): void
    {
        if (!empty($_POST['email']) && !empty($_POST['password'])) {
            $model = new UsuarioSistemaModel();
            $usuario = $model->findByEmail($_POST['email']);

            if ($usuario === false) {
                $respuesta = new Respuesta(403);
            } elseif (password_verify($_POST['password'], $usuario['pass'])) {
                $respuesta = new Respuesta(200);
                $payload = [
                    'id_usuario' => $usuario['id_usuario'],
                    'id_rol' => $usuario['id_rol'],
                    'idioma' => 'es'
                ];
                $token = JWTHelper::getToken($payload);
                $respuesta->setData(['token' => $token]);
            } else {
                $respuesta = new Respuesta(403);
            }
        } else {
            $respuesta = new Respuesta(400);
        }
        $this->view->show('json.view.php', ['respuesta' => $respuesta]);
    }

    public static function getPermisos(int $id_rol): array
    {
        $permisos = [];
        if ($id_rol === self::ID_ADMIN) {
            $permisos = ['proveedor.get', 'proveedor.post', 'proveedor.patch', 'proveedor.put', 'proveedor.delete'];
        } elseif ($id_rol === self::ID_AUDITOR) {
            $permisos = ['proveedor.get'];
        } elseif ($id_rol === self::ID_FACTURACION) {
            $permisos = ['proveedor.get', 'proveedor.post', 'proveedor.patch'];
        }
        return $permisos;
    }

    public function changePassword(int $id_usuario): void
    {
        $put = $this->getParams();
        $errors = [];
        $model = new UsuarioSistemaModel();
        if (empty($put['old_password'])) {
            $errors['old_password'] = 'La contraseña antigua no puede estar vacía';
        } else {
            $usuario = $model->findById($id_usuario);

            if ($usuario === false) {
                throw new \Exception('Usuario no encontrado');
            } else {
                if (!password_verify($put['old_password'], $usuario['pass'])) {
                    $errors['old_password'] = "La contraseña antigua no coincide con la guardada";
                } elseif (empty($put['new_password'])) {
                    $errors['new_password'] = "La nueva contraseña no puede estar vacía";
                } elseif (!preg_match('/^(?=.*[a-z])(?=.*\d).{8,}$/', $put['new_password'])) {
                    $errors['new_password'] = "La contraseña debe ser de al menos 8 caracteres y contener al menos 1 
                letra y 1 numero";
                }
            }
        }

        if ($errors === []) {
            if ($model->changePassword($id_usuario, $put['new_password'])) {
                $respuesta = new Respuesta(200);
            } else {
                $respuesta = new Respuesta(500);
            }
        } else {
            if (isset($errors['old_password'])) {
                $respuesta = new Respuesta(403);
                $respuesta->setData(['mensaje' => $errors['old_password']]);
            } else {
                $respuesta = new Respuesta(400);
                $respuesta->setData(['errores' => $errors]);
            }
        }

        $this->view->show('json.view.php', ['respuesta' => $respuesta]);
    }
}
