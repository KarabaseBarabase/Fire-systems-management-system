<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class ChangeLog
{
    use Creatable;
    public int $logId;
    public string $tableName;
    public string $recordUuid;
    public string $action;
    public array $changedFields;
    public ?int $changedBy = null;
    public \DateTimeImmutable $changedAt;

    public function __construct(
        int $logId,
        string $tableName,
        string $recordUuid,
        string $action,
        array $changedFields,
        \DateTimeImmutable $changedAt,
        ?int $changedBy = null
    ) {
        $this->logId = $logId;
        $this->tableName = $tableName;
        $this->recordUuid = $recordUuid;
        $this->action = $action;
        $this->changedFields = $changedFields;
        $this->changedBy = $changedBy;
        $this->changedAt = $changedAt;
    }
}