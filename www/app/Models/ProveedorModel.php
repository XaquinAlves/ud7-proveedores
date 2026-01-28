<?php

declare(strict_types=1);

namespace Com\Daw2\Models;

use Com\Daw2\Core\BaseDbModel;

class ProveedorModel extends BaseDbModel
{
    private const ORDER_BY = ['cif', 'codigo', 'nombre', 'pais'];
    private const SELECT_FROM = 'SELECT cif, codigo, nombre, direccion, website, email, telefono, id_country, 
                pais.country_code, pais.country_name
                FROM proveedor as prv LEFT JOIN aux_countries as pais ON prv.id_country = pais.id';
    private const LIMIT = 20;

    public function getProveedorByFilters(array $filters): array|false
    {
        $sql = self::SELECT_FROM;
        $query = $this->buildQuery($filters);

        if (count($query['conditions']) > 0) {
            $stringConditions = implode(' AND ', $query['conditions']);
            $sql .= " WHERE {$stringConditions}";
        }

        $sql .= ' ORDER BY ' . $this->getOrder($filters);
        $sql .= 'LIMIT' . $this->getPage($filters) . ',' . self::LIMIT;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($query['params']);
        return $stmt->fetchAll();
    }

    public function getProveedorById(int $id): array|false
    {
        $sql = self::SELECT_FROM . " WHERE prv.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    private function buildQuery(array $filters): array
    {
        $params = [];
        $conditions = [];

        if (isset($filters['nombre'])) {
            $conditions[] = "nombre LIKE :nombre";
            $params['nombre'] = "%{$filters['nombre']}%";
        }

        if (isset($filters['pais'])) {
            $conditions[] = "id_country = :pais";
            $params['pais'] = $filters['pais'];
        }

        if (isset($filters['email'])) {
            $conditions[] = "email LIKE :email";
            $params['email'] = "%{$filters['email']}%";
        }

        return ['conditions' => $conditions, 'params' => $params];
    }

    public function getPage(array $filters): int
    {
        if (!isset($filters['page']) || !filter_var($filters['page'], FILTER_VALIDATE_INT) || $filters['page'] < 0) {
            return 0;
        } else {
            return (int)$filters['page'];
        }
    }

    public function getOrder(array $filters): string
    {
        if (
            !isset($filters['order']) || !filter_var($filters['order'], FILTER_VALIDATE_INT) ||
            $filters['order'] < 1 || $filters['order'] > 4
        ) {
            return self::ORDER_BY[0];
        } else {
            return self::ORDER_BY[$filters['order'] - 1];
        }
    }
}
