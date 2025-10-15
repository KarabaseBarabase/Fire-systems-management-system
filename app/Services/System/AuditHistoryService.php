<?php

namespace App\Services\System;

use App\Data\Repositories\ApprovalHistoryRepository;
use App\Data\Repositories\ChangeLogRepository;

class AuditHistoryService
{
    private $changeLogRepo; // (история изменений)
    private $approvalHistoryRepo; // (история подтверждений)
    public function __construct(
        ChangeLogRepository $changeLogRepo,
        ApprovalHistoryRepository $approvalHistoryRepo
    ) {
        $this->changeLogRepo = $changeLogRepo;
        $this->approvalHistoryRepo = $approvalHistoryRepo;
    }

    public function getSystemHistory($system)
    {
        $changeLogs = $this->changeLogRepo->findByTableAndRecord('fire_systems', $system->recordUuid);
        $approvalHistory = $this->approvalHistoryRepo->findByTableAndRecord('fire_systems', $system->systemId);
        return array_merge($changeLogs, $approvalHistory);
        // return [
        //     'changes' => $this->changeLogRepo->findByTableAndRecord('fire_systems', $system->recordUuid),
        //     'approvals' => $this->approvalHistoryRepo->findByTableAndRecord('fire_systems', $system->systemId),
        // ];
    }
}