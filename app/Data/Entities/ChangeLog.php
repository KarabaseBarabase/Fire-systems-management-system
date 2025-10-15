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
    public ?\DateTimeImmutable $changedAt;

    public function __construct(
        int $logId,
        string $tableName,
        string $recordUuid,
        string $action,
        array $changedFields,
        ?\DateTimeImmutable $changedAt = null,
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

    public function toArray(): array
    {
        return [
            'log_id' => $this->logId,
            'table_name' => $this->tableName,
            'record_uuid' => $this->recordUuid,
            'action' => $this->action,
            'changed_fields' => $this->changedFields,
            'changed_by' => $this->changedBy,
            'changed_at' => $this->changedAt?->format('Y-m-d H:i:s'),
            'user_name' => $this->getUserName(), // если есть метод для получения имени пользователя
        ];
    }

    // Дополнительно можно добавить метод для получения имени пользователя
    private function getUserName(): ?string
    {
        // получение имени пользователя по changedBy
        // Например, через репозиторий или кэш
        return null; // временно
    }
}