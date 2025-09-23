<?php
namespace App\Core;

use App\Core\Database;
use PDO;
use PDOException;
use ReflectionClass;
use Illuminate\Support\Facades\Log;
abstract class Repository
{
    protected $db;
    protected $table;
    protected $entityClass;
    protected $primaryKey;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->primaryKey = $this->detectPrimaryKey();
        ;
    }

    protected function getPdo(): PDO
    {
        return $this->db->getPdo();
    }

    protected function detectPrimaryKey(): string
    {
        try {
            $stmt = $this->getPdo()->prepare("
            SELECT a.attname AS column_name
            FROM pg_index i
            JOIN pg_attribute a ON a.attrelid = i.indrelid
                               AND a.attnum = ANY(i.indkey)
            WHERE i.indrelid = :table::regclass
              AND i.indisprimary
        ");
            $stmt->execute(['table' => $this->table]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row['column_name'] ?? 'id';
        } catch (PDOException $e) {
            error_log("Error detecting primary key for {$this->table}: " . $e->getMessage());
            return 'id'; // fallback
        }
    }

    public function find(int $id): ?object
    {
        try {
            $stmt = $this->getPdo()->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? $this->hydrate($data) : null;
        } catch (PDOException $e) {
            error_log("Error finding {$this->table}: " . $e->getMessage());
            return null;
        }
    }

    public function findByUuid(string $uuid): ?object
    {
        try {
            $stmt = $this->getPdo()->prepare("SELECT * FROM {$this->table} WHERE record_uuid = :uuid");
            $stmt->execute(['uuid' => $uuid]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? $this->hydrate($data) : null;
        } catch (PDOException $e) {
            error_log("Error finding {$this->table} by UUID: " . $e->getMessage());
            return null;
        }
    }

    public function findAll(): array
    {
        try {
            $stmt = $this->getPdo()->prepare("SELECT * FROM {$this->table}");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'hydrate'], $results);
        } catch (PDOException $e) {
            error_log("Error finding all {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    public function findBy(array $criteria): array
    {
        try {
            $where = [];
            $params = [];

            foreach ($criteria as $field => $value) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }

            $sql = "SELECT * FROM {$this->table}";
            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'hydrate'], $results);
        } catch (PDOException $e) {
            error_log("Error finding by criteria {$this->table}: " . $e->getMessage());
            return [];
        }
    }
    public function delete(int $id): bool
    {
        try {
            Log::info("Удаление записи из таблицы {$this->table}", [
                'id' => $id,
                'primary_key' => $this->primaryKey,
                'table' => $this->table
            ]);

            $rowCount = \Illuminate\Support\Facades\DB::table($this->table)
                ->where($this->primaryKey, $id)
                ->delete();

            if ($rowCount > 0) {
                Log::info("Запись успешно удалена", [
                    'table' => $this->table,
                    'id' => $id,
                    'deleted_rows' => $rowCount
                ]);
            } else {
                Log::warning("Запись не найдена для удаления", [
                    'table' => $this->table,
                    'id' => $id
                ]);
            }

            return $rowCount > 0;

        } catch (\Exception $e) {
            Log::error("Ошибка при удалении записи", [
                'table' => $this->table,
                'id' => $id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    abstract protected function hydrate(array $data): object;
    abstract protected function toArray(object $entity): array;
    public function save(object $entity): object
    {
        try {
            $data = $this->toArray($entity);
            $primaryKeyValue = $data[$this->primaryKey] ?? null;

            if ($primaryKeyValue) {
                $this->update($data);
                return $this->find($primaryKeyValue);
            } else {
                $id = $this->insert($data);
                return $this->find($id);
            }
        } catch (PDOException $e) {
            error_log("Error saving {$this->table}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function insert(array $data): int
    {
        // Убираем primary key для INSERT
        unset($data[$this->primaryKey]);

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ") 
                RETURNING {$this->primaryKey}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($data);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result[$this->primaryKey];
    }

    protected function update(array $data): bool
    {
        $id = $data[$this->primaryKey];
        unset($data[$this->primaryKey]);

        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = :{$column}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;

        $stmt = $this->getPdo()->prepare($sql);
        return $stmt->execute($data);
    }
}