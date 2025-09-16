<?php
namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class Equipment
{
    use Creatable;
    public int $equipmentId;
    public string $recordUuid;
    public int $systemId;
    public int $typeId;
    public string $model;
    public ?string $serialNumber = null;
    public ?string $location = null;
    public int $quantity = 1;
    public int $productionYear;
    public ?int $productionQuarter = null;
    public int $serviceLifeYears;
    public ?string $controlPeriod = null;
    public ?\DateTimeImmutable $lastControlDate = null;
    public ?string $controlResult = null;
    public ?string $notes = null;
    public ?\DateTimeImmutable $updatedAt = null;
    public ?int $updatedBy = null;

    public function __construct(
        int $equipmentId,
        string $recordUuid,
        int $systemId,
        int $typeId,
        string $model,
        int $productionYear,
        int $serviceLifeYears,
        ?\DateTimeImmutable $updatedAt = null,
        ?string $serialNumber = null,
        ?string $location = null,
        int $quantity = 1,
        ?int $productionQuarter = null,
        ?string $controlPeriod = null,
        ?\DateTimeImmutable $lastControlDate = null,
        ?string $controlResult = null,
        ?string $notes = null,
        ?int $updatedBy = null
    ) {
        $this->equipmentId = $equipmentId;
        $this->recordUuid = $recordUuid;
        $this->systemId = $systemId;
        $this->typeId = $typeId;
        $this->model = $model;
        $this->serialNumber = $serialNumber;
        $this->location = $location;
        $this->quantity = $quantity;
        $this->productionYear = $productionYear;
        $this->productionQuarter = $productionQuarter;
        $this->serviceLifeYears = $serviceLifeYears;
        $this->controlPeriod = $controlPeriod;
        $this->lastControlDate = $lastControlDate;
        $this->controlResult = $controlResult;
        $this->notes = $notes;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
    }
}