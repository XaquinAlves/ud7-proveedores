<?php

declare(strict_types=1);

namespace Com\Daw2\Controllers;

use Com\Daw2\Core\BaseController;
use Com\Daw2\Libraries\Respuesta;
use Com\Daw2\Models\AuxCountriesModel;
use Com\Daw2\Models\ProveedorModel;

class ProveedorController extends BaseController
{
    public function getProveedorByFilters(): void
    {
        $model = new ProveedorModel();

        $errors = $this->checkErrors($_GET);

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

    public function checkErrors(array $data): array
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

        return $errors;
    }
}
