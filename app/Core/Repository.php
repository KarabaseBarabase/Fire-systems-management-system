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
            error_log("=== SAVE METHOD START ===");
            $data = $this->toArray($entity);
            error_log("Data to save: " . json_encode($data));

            $primaryKeyValue = $data[$this->primaryKey] ?? null;
            error_log("Primary key value: " . ($primaryKeyValue ?? 'NULL'));

            if ($primaryKeyValue) {
                error_log("Performing UPDATE");
                $this->update($data);
                $result = $this->find($primaryKeyValue);
                error_log("Update completed, result: " . json_encode($result));
            } else {
                error_log("Performing INSERT");
                $id = $this->insert($data);
                $result = $this->find($id);
                error_log("Insert completed, ID: " . $id);
            }

            error_log("=== SAVE METHOD END ===");
            return $result;

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

        // Привязываем параметры с явным указанием типов
        foreach ($data as $column => $value) {
            if (is_bool($value)) {
                $stmt->bindValue(":{$column}", $value, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue(":{$column}", $value);
            }
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result[$this->primaryKey];
    }

    protected function update(array $data): bool
    {
        error_log("=== UPDATE METHOD START ===");
        $id = $data[$this->primaryKey];
        unset($data[$this->primaryKey]);

        error_log("Updating record ID: " . $id);
        error_log("Update data: " . json_encode($data));

        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = :{$column}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = :id";
        error_log("SQL: " . $sql);

        $stmt = $this->getPdo()->prepare($sql);

        // Привязываем параметры с явным указанием типов
        foreach ($data as $column => $value) {
            if (is_bool($value)) {
                $stmt->bindValue(":{$column}", $value, PDO::PARAM_BOOL);
                error_log("Binding boolean: {$column} = " . ($value ? 'true' : 'false'));
            } else {
                $stmt->bindValue(":{$column}", $value);
                error_log("Binding: {$column} = " . $value);
            }
        }
        $stmt->bindValue(':id', $id);
        error_log("Binding ID: " . $id);

        $result = $stmt->execute();
        $rowCount = $stmt->rowCount();

        error_log("Execute result: " . ($result ? 'true' : 'false'));
        error_log("Rows affected: " . $rowCount);
        error_log("=== UPDATE METHOD END ===");

        return $result;
    }
}