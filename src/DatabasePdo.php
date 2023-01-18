<?php

declare(strict_types=1);

namespace Crutch\DatabasePdo;

use Crutch\Database\Database;
use Crutch\Database\Exception\StorageError;
use PDO;
use PDOStatement;

final class DatabasePdo implements Database
{
    private PDO $pdo;
    private int $transactions = 0;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $query, ?array $parameters = null): int
    {
        $statement = $this->prepare($query);
        if (is_null($parameters)) {
            $statement->execute();
        } else {
            $statement->execute($parameters);
        }
        return $statement->rowCount();
    }

    /**
     * @inheritDoc
     */
    public function fetch(string $query, array $parameters = []): ?array
    {
        $statement = $this->prepare($query);
        $statement->execute($parameters);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(string $query, array $parameters = []): iterable
    {
        $statement = $this->prepare($query);
        $statement->execute($parameters);
        $result = [];

        do {
            /** @var array|false $row */
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if (is_array($row)) {
                $result[] = $row;
            }
        } while ($row !== false);
        return $result;
    }

    public function begin(): void
    {
        if ($this->transactions === 0) {
            $this->pdo->beginTransaction();
        }
        $this->transactions++;
    }

    public function commit(): void
    {
        if ($this->transactions === 0) {
            return;
        }
        $this->transactions--;
        if ($this->transactions === 0) {
            $this->pdo->commit();
        }
    }

    public function rollback(): void
    {
        if ($this->transactions === 0) {
            return;
        }
        $this->transactions--;
        if ($this->transactions === 0) {
            $this->pdo->rollBack();
        }
    }

    /**
     * @param string $query
     * @return PDOStatement
     * @throws StorageError
     */
    private function prepare(string $query): PDOStatement
    {
        $st = $this->pdo->prepare($query);
        if (empty($st)) {
            throw new StorageError('PDO error');
        }
        return $st;
    }

    public function getDriver(): string
    {
        return (string)$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
