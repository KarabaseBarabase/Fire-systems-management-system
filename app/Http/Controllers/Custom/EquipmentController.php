<?php
namespace App\Http\Controllers\Custom;

use App\Services\EquipmentService;
use App\Core\Controller;
use App\Core\Database;
use App\Core\AuthInterface;

class EquipmentController extends Controller
{
    private $equipmentService;

    public function __construct(Database $db, AuthInterface $auth, EquipmentService $equipmentService)
    {
        parent::__construct($db, $auth); // Вызываем родительский конструктор
        $this->equipmentService = $equipmentService;
    }

    public function addEquipment()
    {
        $this->requireAuth();

        try {
            $data = $this->request['body'];
            $equipment = $this->equipmentService->addEquipment($data);
            return $this->jsonResponse($equipment, 201);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getEquipmentBySystem($systemId)
    {
        $this->requireAuth();

        try {
            $equipment = $this->equipmentService->getEquipmentBySystem($systemId);
            return $this->jsonResponse($equipment);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}