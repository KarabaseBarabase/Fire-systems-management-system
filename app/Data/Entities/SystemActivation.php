<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class SystemActivation
{
    use Creatable;
    public int $systemActivationId;
    public string $recordUuid;
    public int $systemId;
    public ?string $location = null;
    public \DateTimeImmutable $activationDate;
    public ?string $reportedBy = null;
    public ?string $notes = null;
    public \DateTimeImmutable $updatedAt;
    public ?int $updatedBy = null;

    public function __construct(
        int $systemActivationId,
        string $recordUuid,
        int $systemId,
        \DateTimeImmutable $activationDate,
        \DateTimeImmutable $updatedAt,
        ?string $location = null,
        ?string $reportedBy = null,
        ?string $notes = null,
        ?int $updatedBy = null
    ) {
        $this->systemActivationId = $systemActivationId;
        $this->recordUuid = $recordUuid;
        $this->systemId = $systemId;
        $this->location = $location;
        $this->activationDate = $activationDate;
        $this->reportedBy = $reportedBy;
        $this->notes = $notes;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
    }
}