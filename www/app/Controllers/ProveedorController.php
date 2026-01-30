<?php

declare(strict_types=1);

namespace Com\Daw2\Controllers;

use Com\Daw2\Core\BaseController;
use Com\Daw2\Libraries\Respuesta;
use Com\Daw2\Models\AuxCountriesModel;
use Com\Daw2\Models\ProveedorModel;
use Com\Daw2\Traits\BaseRestController;

class ProveedorController extends BaseController
{
    use BaseRestController;

    public function getProveedorByFilters(): void
    {
        $model = new ProveedorModel();

        $errors = $this->checkErrorsFilters($_GET);

        if ($errors === []) {
            $respuesta = new Respuesta(200);
            $proveedores = $model->getProveedorByFilters($_GET);
            $respuesta->setData($proveedores);
        } else {
            $respuesta = new Respuesta(400);
            $respuesta->setData(['errores' => $errors]);
        }

        $this->view->show('json.view.php', ['respuesta' => $respuesta]);
    }

    public function getProveedorByCif(string $cif): void
    {
        $model = new ProveedorModel();
        $proveedor = $model->getProveedorByCif($cif);
        if ($proveedor === false) {
            $respuesta = new Respuesta(404);
        } else {
            $respuesta = new Respuesta(200);
            $respuesta->setData($proveedor);
        }

        $this->view->show('json.view.php', ['respuesta' => $respuesta]);
    }

    public function checkErrorsFilters(array $data): array
    {
        $errors = [];

        if (isset($data['pais']) && !filter_var($data['pais'], FILTER_VALIDATE_INT)) {
            $errors['pais'] = 'El pais debe ser un numero entero correspondiente al id_pais';
        } elseif (isset($data['pais']) && !((new AuxCountriesModel())->existsCountry((int)$data['pais']))) {
            $errors['pais'] = 'El pais no existe';
        }

        if (isset($data['page']) && $data['page'] != 0 && !filter_var($data['page'], FILTER_VALIDATE_INT)) {
            $errors['page'] = 'La pagina debe ser un numero entero';
        } elseif (
            isset($data['page']) &&
            ($data['page'] < 0 || $data['page'] > (new ProveedorModel())->getLastPage($data))
        ) {
            $errors['page'] = 'La pagina no existe, paginas de 0 a ' . (new ProveedorModel())->getLastPage($data);
        }

        if (
            isset($data['order']) &&
            (!filter_var($data['order'], FILTER_VALIDATE_INT) || $data['order'] < 1 || $data['order'] > 4)
        ) {
            $errors['order'] = 'El orden debe ser un numero entero entre 1 y 4';
        }

        if (isset($data['sentido']) && $data['sentido'] !== 'asc' && $data['sentido'] !== 'desc') {
            $errors['sentido'] = 'El sentido debe ser asc o desc';
        }

        return $errors;
    }

    public function deleteProveedor(string $cif): void
    {
        $model = new ProveedorModel();
        try {
            if ($model->deleteProveedor($cif)) {
                $respuesta = new Respuesta(200);
            } else {
                $respuesta = new Respuesta(404);
                $respuesta->setData(['mensaje' => 'El proveedor no existe']);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() === 23000) {
                $respuesta = new Respuesta(409);
                $respuesta->setData(['mensaje' => 'El proveedor tiene artículos asociados']);
            } else {
                throw $e;
            }
        }
        $this->view->show('json.view.php', ['respuesta' => $respuesta]);
    }

    public function patchProveedor(string $cif): void
    {
        $patch = $this->getParams();
        $errors = $this->checkErrorsPostPatch($patch, $cif);
        if ($errors !== []) {
            $respuesta = new Respuesta(400);
            $respuesta->setData(['errores' => $errors]);
        } else {
            $model = new ProveedorModel();
            if ($model->updateProveedor($cif, $patch)) {
                $respuesta = new Respuesta(200);
                $respuesta->setData(['mensaje' => 'Proveedor ' . $cif . ' actualizado correctamente']);
            } else {
                $respuesta = new Respuesta(404);
                $respuesta->setData(['mensaje' => 'Proveedor ' . $cif . ' no encontrado']);
            }
        }
    }

    public function checkErrorsPostPatch(array $data, ?string $cif = null): array
    {
        $errors = [];
        $model = new ProveedorModel();

        if (empty($data['cif'])) {
            $errors['cif'] = 'El cif es obligatorio';
        } elseif (preg_match('/^[A-Z][0-9]{7}[A-Z]$/ìu', $data['cif']) === false) {
            $errors['cif'] = 'El cif debe tener el formato L1234567L';
        } else {
            if (($cif !== null && $cif !== $data['cif']) || $cif === null) {
                if ($model->getProveedorByCif($data['cif']) !== false) {
                    $errors['cif'] = 'Ya existe un proveedor con ese cif';
                }
            }
        }

        if (empty($data['codigo'])) {
            $errors['codigo'] = 'El codigo es obligatorio';
        } elseif (preg_match('/.{5,10}/iu', $data['codigo']) === false) {
            $errors['codigo'] = 'El codigo debe tener entre 5 y 10 caracteres';
        } else {
            if ($model->getProveedorByCodigo($data['codigo']) !== false) {
                if ($cif !== null || $cif !== $data['cif']) {
                    $errors['codigo'] = 'Ya existe un proveedor con ese codigo';
                }
            }
        }

        if (empty($data['nombre'])) {
            $errors['nombre'] = 'El nombre es obligatorio';
        } elseif (mb_strlen($data['nombre']) > 255) {
            $errors['nombre'] = 'El nombre debe tener un máximo de 255 caracteres';
        }

        if (empty($data['direccion'])) {
            $errors['direccion'] = 'La direccion es obligatoria';
        } elseif (mb_strlen($data['direccion']) > 255) {
            $errors['direccion'] = 'La direccion debe tener un máximo de 255 caracteres';
        }

        if (empty($data['web'])) {
            $errors['web'] = 'La web es obligatoria';
        } elseif (mb_strlen($data['web'] > 255)) {
            $errors['web'] = 'La web debe tener un máximo de 255 caracteres';
        } elseif (filter_var($data['web'], FILTER_VALIDATE_URL) === false) {
            $errors['web'] = 'La web debe ser una url valida';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email es obligatoria';
        } elseif (mb_strlen($data['email'] > 255)) {
            $errors['web'] = 'El email debe tener un máximo de 255 caracteres';
        } elseif (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['web'] = 'El email debe tener un formato correcto de email';
        }

        if (!empty($data['telefono'])) {
            if (preg_match('/([0-9]{9})|([0-9]{12})/iu', $data['telefono']) === false) {
                $errors['telefono'] = 'El telefono debe tener 9 o 12 digitos';
            }
        }

        if (empty($data['pais'])) {
            $errors['pais'] = 'El pais es obligatorio';
        } elseif (!filter_var($data['pais'], FILTER_VALIDATE_INT)) {
            $errors['pais'] = 'El pais debe ser un numero entero positivo correspondiente al id_pais';
        } elseif (!((new AuxCountriesModel())->existsCountry((int)$data['pais']))) {
            $errors['pais'] = 'El pais no se encuentra en la base de datos';
        }

        return $errors;
    }
}
