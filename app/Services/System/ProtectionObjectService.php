<?php

namespace App\Services\System;

use App\Data\Repositories\CuratorRepository;
use App\Data\Repositories\ObjectGroupRepository;
use App\Data\Repositories\ProtectionObjectRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProtectionObjectService
{
    private $protectionObjectRepo; // (объекты защиты)
    private $objectGroupRepo; // (группы объектов)
    private $curatorRepo; // (кураторы)

    public function __construct
    (
        ProtectionObjectRepository $protectionObjectRepo,
        ObjectGroupRepository $objectGroupRepo,
        CuratorRepository $curatorRepo,
    ) {
        $this->protectionObjectRepo = $protectionObjectRepo;
        $this->objectGroupRepo = $objectGroupRepo;
        $this->curatorRepo = $curatorRepo;
    }

    public function getAllObjectGroups(): array
    {
        return $this->objectGroupRepo->findAll();
    }
    public function getObjectWithDetails($objectId)
    {
        $object = $this->protectionObjectRepo->find($objectId);

        if (!$object) {
            return null;
        }

        return [
            'object' => $object,
            'group' => $object->objectGroupId ? $this->objectGroupRepo->find($object->objectGroupId) : null,
            'curator' => $object->curatorId ? $this->curatorRepo->find($object->curatorId) : null,
        ];
    }

    public function getAllProtectionObjects()
    {
        return $this->protectionObjectRepo->findAll();
    }

    public function getAllCurators()
    {
        return $this->curatorRepo->findAll();
    }
}