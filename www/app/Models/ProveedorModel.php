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

    public function getProveedorByFilters(array $filters): array
    {
        $sql = self::SELECT_FROM;
        $query = $this->buildQuery($filters);

        if (count($query['conditions']) > 0) {
            $stringConditions = implode(' AND ', $query['conditions']);
            $sql .= " WHERE $stringConditions";
        }

        $sql .= ' ORDER BY ' . $this->getOrder($filters);
        if (!empty($filters['sentido'])) {
            $sql .= ' ' . $filters['sentido'];
        }
        $sql .= ' LIMIT ' . $this->getPage($filters) . ',' . self::LIMIT;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($query['params']);
        return $stmt->fetchAll();
    }

    public function getProveedorByCif(string $cif): array|false
    {
        $sql = self::SELECT_FROM . " WHERE prv.cif = :cif";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cif' => $cif]);
        return $stmt->fetch();
    }

    public function getProveedorByCodigo(string $codigo): array|false
    {
        $sql = self::SELECT_FROM . " WHERE prv.codigo = :codigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['codigo' => $codigo]);
        return $stmt->fetch();
    }

    public function insertProveedor(array $data): bool
    {
        $sql = "INSERT INTO proveedor (cif, codigo, nombre, direccion, website, email, telefono, id_country)
            VALUES (:cif, :codigo, :nombre, :direccion, :website, :email, :telefono, :id_country)";
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'cif' => $data['cif'],
            'codigo' => $data['codigo'],
            'nombre' => $data['nombre'],
            'direccion' => $data['direccion'],
            'website' => $data['web'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'id_country' => $data['pais']
        ];
        return $stmt->execute($params);
    }

    public function updateProveedor(string $cif, array $data): bool
    {
        $sql = "UPDATE proveedor ";
        $params = [];
        $conditions = [];

        if (isset($data['cif'])) {
            $conditions[] = "cif = :cifNuevo ";
            $params['cifNuevo'] = $cif;
        }

        if (isset($data['codigo'])) {
            $conditions[] = "codigo = :codigo ";
            $params['codigo'] = $data['codigo'];
        }

        if (isset($data['nombre'])) {
            $conditions[] = "nombre = :nombre ";
            $params['nombre'] = $data['nombre'];
        }

        if (isset($data['direccion'])) {
            $conditions[] = "direccion = :direccion ";
            $params['direccion'] = $data['direccion'];
        }

        if (isset($data['web'])) {
            $conditions[] = "website = :website ";
            $params['website'] = $data['web'];
        }

        if (isset($data['email'])) {
            $conditions[] = "email = :email ";
            $params['email'] = $data['email'];
        }

        if (isset($data['telefono'])) {
            $conditions[] = "telefono = :telefono ";
            $params['telefono'] = $data['telefono'];
        }

        if (isset($data['pais'])) {
            $conditions[] = "id_country = :pais ";
            $params['pais'] = $data['pais'];
        }

        if (count($conditions) > 0) {
            $sql .= "SET " . implode(', ', $conditions) . ' WHERE cif = :cifViejo ';
            $params['cifViejo'] = $cif;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } else {
            return true;
        }
    }

    private function buildQuery(array $filters): array
    {
        $params = [];
        $conditions = [];

        if (!empty($filters['nombre'])) {
            $conditions[] = "nombre LIKE :nombre";
            $params['nombre'] = "%{$filters['nombre']}%";
        }

        if (!empty($filters['pais'])) {
            $conditions[] = "id_country = :pais";
            $params['pais'] = $filters['pais'];
        }

        if (!empty($filters['email'])) {
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

    public function getLastPage(array $filters): int
    {
        $sql = "SELECT COUNT(*) FROM proveedor";
        $query = $this->buildQuery($filters);

        if (count($query['conditions']) > 0) {
            $stringConditions = implode(' AND ', $query['conditions']);
            $sql .= " WHERE $stringConditions";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($query['params']);
        $numRows = (int)$stmt->fetchColumn();
        return (int)ceil($numRows / self::LIMIT);
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

    public function deleteProveedor(string $cif): bool
    {
        $sql = "DELETE FROM proveedor WHERE cif = :cif";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['cif' => $cif]);
    }
}
