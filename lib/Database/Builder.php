<?php

namespace Lib\Database;

use Countable;
use InvalidArgumentException;
use PDO, PDOStatement;
use UnexpectedValueException;

class Builder implements Countable
{
    private PDO $pdo;

    private PDOStatement $statement;

    /**
     * @param PDO|null $pdo
     */
    public function __construct(PDO $pdo = null)
    {
        if ($pdo === null) {
            $pdo = new PDO(
                'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'],
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD'],
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
    public function __invoke($queries): self
    {
        switch (true) {
            case is_string($queries): {
                $statement = $this->pdo->prepare($queries);
                break;
            }
            case is_array($queries): {
                array_walk($queries, function (&$value, $key): void {
                    $value = is_string($key) ? $key . ' ' . $value : $value;
                });
                $queries = implode(' ', array_values($queries));
                $statement = $this->pdo->prepare($queries);
                break;
            }
            default:
                throw new InvalidArgumentException;
        }

        if ($statement === false) {
            throw new UnexpectedValueException;
        }
        $this->statement = $statement;

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

    public function execute(): bool
    {
        return $this->statement->execute();
    }

    public function fetchAll(): array
    {
        $this->execute();

        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetch(): array
    {
        $this->execute();

        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    public function count(): int
    {
        return $this->statement->rowCount();
    }
}
