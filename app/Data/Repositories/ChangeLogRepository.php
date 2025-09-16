<?php
namespace App\Data\Repositories;

use App\Data\Entities\ChangeLog;
use App\Core\Repository;

class ChangeLogRepository extends Repository
{
    protected $table = 'change_log';
    protected $entityClass = ChangeLog::class;

    public function findByTableAndRecord(string $tableName, string $recordUuid): array
    {
        return $this->findBy([
            'table_name' => $tableName,
            'record_uuid' => $recordUuid
        ]);
    }

    public function findByUser(int $userId): array
    {
        return $this->findBy(['changed_by' => $userId]);
    }

    protected function hydrate(array $data): ChangeLog
    {
        $log = ChangeLog::createEmpty();
        $log->logId = (int) $data['log_id'];
        $log->tableName = $data['table_name'];
        $log->recordUuid = $data['record_uuid'];
        $log->action = $data['action'];
        $log->changedFields = json_decode($data['changed_fields'], true);
        $log->changedBy = $data['changed_by'] ? (int) $data['changed_by'] : null;
        $log->changedAt = $data['changed_at'];

        return $log;
    }

    protected function toArray(object $entity): array
    {
        return [
            'table_name' => $entity->tableName,
            'record_uuid' => $entity->recordUuid,
            'action' => $entity->action,
            'changed_fields' => json_encode($entity->changedFields),
            'changed_by' => $entity->changedBy,
            'changed_at' => $entity->changedAt
        ];
    }
}