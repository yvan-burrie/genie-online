<?php

namespace Lib\Database;

use Countable;
use PDO, PDOStatement;

class Builder implements Countable
{
    private PDO $pdo;

    private PDOStatement $statement;

    /**
     * @param PDO|null $pdo
     */
    protected function __construct(PDO $pdo = null)
    {
        if ($pdo === null) {
            $pdo = new PDO(
                'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_NAME'),
                env('DB_USER'),
                env('DB_PASS'),
                [
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
        }
        $this->pdo = $pdo;
    }

    /**
     * @param string|string[]|array<string, string|string[]> $table
     */
    public function __invoke(mixed $queries): self
    {
        switch (true) {
            case is_string($queries): {
                $this->statement = $this->pdo->prepare($queries);
                break;
            }
            case is_array($queries): {
                foreach ($queries as $a => &$b) {
                    if (is_string($a)) {
                        $b = strtoupper($a) . ' ' . $b;
                        continue;
                    }
                    $b = is_array($b) ? implode(' ', $b) : $b;
                }
                $this->statement = $this->pdo->prepare($queries);
                break;
            }
        }

        return $this;
    }

    public function __set(mixed $parameter, mixed $value): void
    {
        if (is_array($parameter)) {
            foreach ($parameter as $key => $value) {
                $this->statement->bindValue($key, $value);
            }
            return;
        }
        switch (true) {
            case is_int($value):
                $type = PDO::PARAM_INT;
                break;
            case is_bool($value):
                $type = PDO::PARAM_BOOL;
                break;
            case is_null($value):
                $type = PDO::PARAM_NULL;
                break;
            default:
                $type = PDO::PARAM_STR;
        }
        $this->statement->bindValue($parameter, $value, $type);
    }

    public function fetchAll()
    {
        $this->statement->execute();

        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetch()
    {
        $this->statement->execute();

        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    public function count(): int
    {
        return $this->statement->rowCount();
    }
}
