<?php

declare(strict_types=1);

namespace Com\Daw2\Models;

use Com\Daw2\Core\BaseDbModel;

class UsuarioSistemaModel extends BaseDbModel
{
    public function findById(int $id): array|false
    {
        $sql = "SELECT * FROM usuario_sistema WHERE id_usuario = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();
        if ($usuario !== false) {
            $usuario['permisos'] = $this->getPermisos((int)$usuario['id_rol']);
        }
        return $usuario;
    }

    public function findByEmail(string $email): array|false
    {
        $sql = "SELECT * FROM usuario_sistema WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
        return $usuario;
    }

    public function getPermisos(int $id_rol): array
    {
        if ($id_rol == 1) {
            $permisos = ['proveedor' => 'rwd'];
        } elseif ($id_rol == 2) {
            $permisos = ['proveedor' => 'r'];
        } elseif ($id_rol == 3) {
            $permisos = ['proveedor' => 'rwd'];
        }

        return $permisos;
    }
}
