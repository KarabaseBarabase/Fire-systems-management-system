<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class ApprovalHistory
{
    use Creatable;
    public int $approvalId;
    public string $tableName;
    public int $recordId;
    public string $curatorType;
    public ?string $oldStatus = null;
    public string $newStatus;
    public ?int $approvedBy = null;
    public \DateTimeImmutable $approvedAt;
    public ?string $comment = null;

    public function __construct(
        int $approvalId,
        string $tableName,
        int $recordId,
        string $curatorType,
        string $newStatus,
        \DateTimeImmutable $approvedAt,
        ?string $oldStatus = null,
        ?int $approvedBy = null,
        ?string $comment = null
    ) {
        $this->approvalId = $approvalId;
        $this->tableName = $tableName;
        $this->recordId = $recordId;
        $this->curatorType = $curatorType;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = $approvedAt;
        $this->comment = $comment;
    }
}