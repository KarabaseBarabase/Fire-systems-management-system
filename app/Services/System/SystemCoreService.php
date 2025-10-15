<?php

namespace App\Services\System;

use App\Data\Repositories\{
    FireSystemRepository,
    SystemSubtypeRepository,
    SystemTypeRepository,
    RegulationRepository,
    BranchRepository
};

class SystemCoreService
{
    private $fireSystemRepo;
    private $systemTypeRepo;
    private $systemSubtypeRepo;
    private $regulationRepo;
    private $branchRepo;

    public function __construct(
        FireSystemRepository $fireSystemRepo,
        SystemTypeRepository $systemTypeRepo,
        SystemSubtypeRepository $systemSubtypeRepo,
        BranchRepository $branchRepo,
        RegulationRepository $regulationRepo
    ) {
        $this->fireSystemRepo = $fireSystemRepo;
        $this->systemTypeRepo = $systemTypeRepo;
        $this->systemSubtypeRepo = $systemSubtypeRepo;
        $this->branchRepo = $branchRepo;
        $this->regulationRepo = $regulationRepo;
    }

    public function getSystemWithDetails($systemId)
    {
        $system = $this->fireSystemRepo->find($systemId);

        if (!$system) {
            return null;
        }

        $subtype = $this->systemSubtypeRepo->find($system->subtypeId);
        $type = $subtype ? $this->systemTypeRepo->find($subtype->typeId) : null;

        $branch = null;
        if ($system->objectId) {
            $branch = $this->branchRepo->findByProtectionObjectId($system->objectId);
        }
        return [
            'system' => $system,
            'subtype' => $subtype,
            'type' => $type,
            'branch' => $branch,
            'regulations' => $this->regulationRepo->findAll(),
            'documents' => $this->fireSystemRepo->getSystemDocuments($systemId),
        ];
    }

    public function getSystemById($systemId)
    {
        return $this->fireSystemRepo->find($systemId);
    }

    public function getSystemByUuid($uuid)
    {
        return $this->fireSystemRepo->findByUuid($uuid);
    }

    public function getAllSystemTypes(): array
    {
        return $this->systemTypeRepo->findAll();
    }

    public function getAllSystemSubtypes(): array
    {
        return $this->systemSubtypeRepo->findAll();
    }

    public function getAllBranches(): array
    {
        return $this->branchRepo->findAll();
    }

    public function getAllRegulations(): array
    {
        return $this->regulationRepo->findAll();
    }
    public function createSystem(array $data): \App\Data\Entities\FireSystem
    {
        $this->validateSystemData($data);

        $fireSystem = new \App\Data\Entities\FireSystem(
            0, // systemId будет установлен базой данных
            '', // сгенерирует база данных
            $data['is_part_of_object'] ?? false,
            new \DateTimeImmutable(),
            $data['object_id'] ?? null,
            $data['subtype_id'] ?? null,
            $data['system_inventory_number'] ?? null,
            $data['name'] ?? null,
            $data['manual_file_link'] ?? null,
            $data['maintenance_schedule_file_link'] ?? null,
            $data['test_program_file_link'] ?? null,
            null // updatedBy будет установлен триггером базы данных
        );

        return $this->fireSystemRepo->save($fireSystem);
    }
    private function validateSystemData(array $data): void
    {
        $required = ['subtype_id', 'is_part_of_object'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Отсутствует обязательное поле: $field");
            }
        }

        if ($data['is_part_of_object'] && empty($data['object_id'])) {
            throw new \InvalidArgumentException('Для системы в составе объекта обязателен object_id');
        }

        // Дополнительные проверки можно добавить здесь
    }
    public function updateSystem(int $systemId, array $data): bool
    {
    }
    public function deleteSystem(int $systemId): bool
    {
    }

}