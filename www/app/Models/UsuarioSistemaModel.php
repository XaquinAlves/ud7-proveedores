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
        return $stmt->fetch();
    }

    public function findByEmail(string $email): array|false
    {
        $sql = "SELECT * FROM usuario_sistema WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function changePassword(int $id, string $password): bool
    {
        $sql = "UPDATE usuario_sistema SET pass = :password WHERE id_usuario = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $id
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
