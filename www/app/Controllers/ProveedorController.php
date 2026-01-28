<?php

declare(strict_types=1);

namespace Com\Daw2\Controllers;

use Com\Daw2\Core\BaseController;
use Com\Daw2\Models\ProveedorModel;

class ProveedorController extends BaseController
{
    public function getProveedorByFilters(): void
    {
        $model = new ProveedorModel();
        $proveedores = $model->getProveedorByFilters($_GET);
    }

    public function getProveedorById(): void
    {
        $model = new ProveedorModel();
        $proveedor = $model->getProveedorById($_GET['id']);
        $this->view->show('json.view.php', ['respuesta' => $proveedor]);
    }

    public function checkErrors(array $data): array
    {
        $errors = [];


        return $errors;
    }
}
