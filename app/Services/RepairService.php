<?php
namespace App\Services;

use App\Data\Repositories\RepairRepository;
use App\Data\Repositories\MountRepository;
use App\Data\Repositories\FireSystemRepository;
use App\Data\Entities\Repair;
use App\Data\Entities\Mount;

class RepairService
{
    private $repairRepo;
    private $mountRepo;
    private $fireSystemRepo;

    public function __construct(
        RepairRepository $repairRepo,
        MountRepository $mountRepo,
        FireSystemRepository $fireSystemRepo
    ) {
        $this->repairRepo = $repairRepo;
        $this->mountRepo = $mountRepo;
        $this->fireSystemRepo = $fireSystemRepo;
    }

    public function createRepair(array $data): Repair
    {
        $this->validateRepairData($data);

        // Проверка прав доступа
        if (!$this->canCreateRepair($data['system_id'])) {
            throw new \Exception('Недостаточно прав для создания ремонта');
        }

        // Создаем объект Repair из данных
        $repair = new Repair(
            0, // repairId будет установлен базой данных
            '', // record_uuid - база данных сгенерирует сама
            $data['system_id'],
            $data['work_type'],
            $data['execution_method'],
            $data['planned_year'],
            $data['status'],
            $data['cost'] ?? null,
            $data['installation_org_id'] ?? null,
            $data['completion_date'] ?? null,
            $data['act_file_link'] ?? null,
            $data['equipment_list_file_link'] ?? null,
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'), // updatedAt как строка
            null // updatedBy будет установлен триггером базы данных
        );

        $repair = $this->repairRepo->save($repair);

        // Если ремонт выполнен, создаем запись о монтаже
        if ($data['status'] === 'выполнен') {
            $this->createMountForRepair($repair);
        }

        return $repair;
    }

    public function updateRepairStatus(string $uuid, string $status, string $comment = null): Repair
    {
        $repair = $this->repairRepo->findByUuid($uuid);
        if (!$repair) {
            throw new \Exception('Ремонт не найден');
        }

        // Проверка прав подтверждения
        if (!$this->canConfirmRepair($repair)) {
            throw new \Exception('Недостаточно прав для подтверждения ремонта');
        }

        $repair->status = $status;
        $this->repairRepo->save($repair);

        // Логирование подтверждения
        $this->logApproval($repair, $status, $comment);

        return $repair;
    }

    private function createMountForRepair(Repair $repair): void
    {
        // Преобразуем completionDate в DateTimeImmutable, если это строка
        $commissionDate = $repair->completionDate;
        if (is_string($commissionDate)) {
            $commissionDate = new \DateTimeImmutable($commissionDate);
        }

        // Если completionDate null, используем текущую дату или генерируем исключение
        if ($commissionDate === null) {
            throw new \InvalidArgumentException('Completion date is required for mount creation');
        }

        $mount = Mount::createEmpty();
        $mount->systemId = $repair->systemId;
        $mount->installationOrgId = $repair->installationOrgId;
        $mount->commissionDate = $commissionDate;
        $mount->actFileLink = $repair->actFileLink;
        $mount->equipmentListFileLink = $repair->equipmentListFileLink;
        $mount->status = 'ожидает проверки';
        $mount->repairId = $repair->repairId;
        $mount->repairWorkType = $repair->workType;
        $mount->repairExecutionMethod = $repair->executionMethod;

        $this->mountRepo->save($mount);
    }

    private function validateRepairData(array $data): void
    {
        $required = ['system_id', 'work_type', 'execution_method', 'planned_year', 'status'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Обязательное поле: $field");
            }
        }
    }

    private function canCreateRepair(int $systemId): bool
    {
        // Логика проверки прав
        return true;
    }

    private function canConfirmRepair(Repair $repair): bool
    {
        // Логика проверки прав подтверждения
        return true;
    }

    private function logApproval(Repair $repair, string $status, ?string $comment): void
    {
        // Логирование в approval_history
    }
}