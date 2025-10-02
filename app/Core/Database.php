<?php
namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private $pdo;
    private $transactionLevel = 0;

    public function __construct(array $config)
    {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => $config['persistent'] ?? false,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);

            // Устанавливаем кодировку
            $this->pdo->exec("SET NAMES 'UTF8'");

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /* Устанавливает идентификатор текущего пользователя для PostgreSQL сессии */
    // public function setCurrentUserId(int $userId): bool
    // {
    //     try {
    //         $stmt = $this->pdo->prepare("SET app.current_user_id = $1");

    //         return $stmt->execute([$userId]);
    //     } catch (PDOException $e) {
    //         error_log("Failed to set current user ID: " . $e->getMessage());
    //         return false;
    //     }
    // }
    public function setCurrentUserId(int $userId): void
    {
        try {
            $sql = "SET app.current_user_id = " . (int) $userId;
            $this->getPdo()->exec($sql);

            $checkSql = "SELECT current_setting('app.current_user_id', true) as user_id";
            $result = $this->fetch($checkSql);

            error_log("Successfully set current user ID to: " . $userId . ", DB confirms: " . ($result['user_id'] ?? 'NULL'));

        } catch (PDOException $e) {
            error_log("Failed to set current user ID: " . $e->getMessage());
            throw $e;
        }
    }
    /* Начинает транзакцию с поддержкой вложенных транзакций */
    public function beginTransaction(): bool
    {
        if ($this->transactionLevel === 0) {
            $result = $this->pdo->beginTransaction();
        } else {
            $result = $this->pdo->exec("SAVEPOINT LEVEL{$this->transactionLevel}") !== false;
        }

        if ($result) {
            $this->transactionLevel++;
        }

        return $result;
    }

    /* Коммитит транзакцию */
    public function commit(): bool
    {
        if ($this->transactionLevel === 0) {
            throw new \RuntimeException("No active transaction");
        }

        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            return $this->pdo->commit();
        }

        return $this->pdo->exec("RELEASE SAVEPOINT LEVEL{$this->transactionLevel}") !== false;
    }

    /* Откатывает транзакцию */
    public function rollBack(): bool
    {
        if ($this->transactionLevel === 0) {
            throw new \RuntimeException("No active transaction");
        }

        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            return $this->pdo->rollBack();
        }

        return $this->pdo->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transactionLevel}") !== false;
    }

    /* Выполняет запрос и возвращает количество затронутых строк */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /* Выполняет запрос и возвращает одну строку */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    /* Выполняет запрос и возвращает все строки */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /* Выполняет запрос и возвращает значение первой колонки первой строки */
    public function fetchColumn(string $sql, array $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /* Возвращает ID последней вставленной записи */
    public function lastInsertId(?string $name = null): string
    {
        return $this->pdo->lastInsertId($name);
    }

    /* Проверяет, находится ли соединение в транзакции */
    public function inTransaction(): bool
    {
        return $this->transactionLevel > 0;
    }

    /* Закрывает соединение с базой данных */
    public function close(): void
    {
        $this->pdo = null;
    }

    /* Экранирование строки для использования в SQL-запросах */
    public function quote(string $string): string
    {
        return $this->pdo->quote($string);
    }


}