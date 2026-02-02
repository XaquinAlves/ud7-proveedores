<?php

declare(strict_types=1);

namespace Com\Daw2\Models;

use Com\Daw2\Core\BaseDbModel;

class AuxCountriesModel extends BaseDbModel
{
    public function existsCountry(int $id): bool
    {
        $sql = "SELECT id FROM aux_countries WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
