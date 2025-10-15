<?php
namespace App\Data\Repositories;

use App\Data\Entities\Branch;
use App\Core\Repository;
use PDO;
class BranchRepository extends Repository
{
    protected $table = 'branches';
    protected $entityClass = Branch::class;

    public function findByProtectionObjectId($objectId)
    {
        $result = \Illuminate\Support\Facades\DB::table('protection_objects')
            ->select('branches.*')
            ->join('branches', 'protection_objects.branch_id', '=', 'branches.branch_id')
            ->where('protection_objects.object_id', $objectId)
            ->first();

        if (!$result) {
            return null;
        }

        return new Branch(
            branchId: $result->branch_id,
            name: $result->name,
            shortName: $result->short_name
        );
    }

    protected function hydrate(array $data): Branch
    {
        $branch = Branch::createEmpty();
        $branch->branchId = (int) $data['branch_id'];
        $branch->name = $data['name'];
        $branch->shortName = $data['short_name'] ?? null;

        return $branch;
    }

    protected function toArray(object $entity): array
    {
        return [
            'branch_id' => $entity->branchId,
            'name' => $entity->name,
            'short_name' => $entity->shortName
        ];
    }

    public function getAllBranches(): array
    {
        try {
            $sql = "SELECT branch_id, name, short_name FROM branches ORDER BY branch_id";
            $stmt = $this->getPdo()->query($sql);

            $branches = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $branches[] = $this->hydrate($row);
            }

            return $branches;

        } catch (\PDOException $e) {
            // Логирование ошибки
            error_log("BranchRepository getAllBranches error: " . $e->getMessage());
            return [];
        }
    }

}