<?php

declare(strict_types=1);

namespace Com\Daw2\Controllers;

use Com\Daw2\Core\BaseController;
use Com\Daw2\Libraries\JWTHelper;
use Com\Daw2\Libraries\Respuesta;
use Com\Daw2\Models\UsuarioSistemaModel;

class UsuarioSistemaController extends BaseController
{
    public function login(): void
    {
        if (!empty($_POST['email']) && !empty($_POST['password'])) {
            $model = new UsuarioSistemaModel();
            $usuario = $model->findByEmail($_POST['email']);

            if ($usuario === false) {
                $respuesta = new Respuesta(403);
            } elseif (password_verify($_POST['password'], $usuario['pass'])) {
                $respuesta = new Respuesta(200);
                $payload = ['id_usuario' => $usuario['id_usuario']];
                $token = (new JWTHelper())->getToken($payload);
                $respuesta->setData(['token' => $token]);
            } else {
                $respuesta = new Respuesta(403);
            }
        } else {
            $respuesta = new Respuesta(400);
        }
        $this->view->show('json.view.php', ['respuesta' => $respuesta]);
    }
}
