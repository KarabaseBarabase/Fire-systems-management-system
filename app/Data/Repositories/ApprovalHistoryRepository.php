<?php
namespace App\Data\Repositories;

use App\Data\Entities\ApprovalHistory;
use App\Core\Repository;

class ApprovalHistoryRepository extends Repository
{
    protected $table = 'approval_history';
    protected $entityClass = ApprovalHistory::class;

    public function findByTableAndRecord(string $tableName, int $recordId): array
    {
        return $this->findBy([
            'table_name' => $tableName,
            'record_id' => $recordId
        ]);
    }

    public function findByApprover(int $userId): array
    {
        return $this->findBy(['approved_by' => $userId]);
    }

    protected function hydrate(array $data): ApprovalHistory
    {
        $history = ApprovalHistory::createEmpty();
        $history->approvalId = (int) $data['approval_id'];
        $history->tableName = $data['table_name'];
        $history->recordId = (int) $data['record_id'];
        $history->curatorType = $data['curator_type'];
        $history->oldStatus = $data['old_status'] ?? null;
        $history->newStatus = $data['new_status'];
        $history->approvedBy = $data['approved_by'] ? (int) $data['approved_by'] : null;
        $history->approvedAt = $data['approved_at'];
        $history->comment = $data['comment'] ?? null;

        return $history;
    }

    protected function toArray(object $entity): array
    {
        return [
            'table_name' => $entity->tableName,
            'record_id' => $entity->recordId,
            'curator_type' => $entity->curatorType,
            'old_status' => $entity->oldStatus,
            'new_status' => $entity->newStatus,
            'approved_by' => $entity->approvedBy,
            'approved_at' => $entity->approvedAt,
            'comment' => $entity->comment
        ];
    }
}