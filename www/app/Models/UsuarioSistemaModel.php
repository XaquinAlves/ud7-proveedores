<?php

declare(strict_types=1);

namespace Com\Daw2\Models;

use Com\Daw2\Core\BaseDbModel;

class UsuarioSistemaModel extends BaseDbModel
{
    public function findById(int $id): array|false
    {
        $sql = "SELECT * FROM usuario_sistema WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();
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
}
