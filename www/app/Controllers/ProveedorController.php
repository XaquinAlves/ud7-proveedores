<?php

declare(strict_types=1);

namespace Com\Daw2\Controllers;

use Com\Daw2\Core\BaseController;
use Com\Daw2\Libraries\Respuesta;
use Com\Daw2\Models\ProveedorModel;

class ProveedorController extends BaseController
{
    public function getProveedorByFilters(): void
    {
        $model = new ProveedorModel();
        $proveedores = $model->getProveedorByFilters($_GET);
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


        return $errors;
    }
}
