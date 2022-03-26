<?php

namespace Lib\Database;

use BadMethodCallException;

abstract class Model
{
    /**
     * @var string
     */
    protected static string $table = 'models';

    /**
     * @var array<string, string[]>
     */
    protected static array $schema = [
        'id' => ['integer', 'primary'],
    ];

    /**
     * @var Builder
     */
    protected Builder $builder;

    /**
     * @var bool
     */
    protected bool $exists = false;

    /**
     * @var array<string, mixed>
     */
    protected array $temp = [];

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @return string
     */
    public static function getPrimaryKey(): string
    {
        return 'id';
    }

    /**
     * @return mixed
     */
    public function getPrimaryValue(): mixed
    {
        return $this->data[static::getPrimaryKey()];
    }

    /**
     * @return static
     */
    public static function make(array $data): self
    {
        $that = new static;

        $that->data = array_filter($data, function (mixed $key): bool {
            return array_key_exists($key, static::$schema);
        }, ARRAY_FILTER_USE_KEY);

        return $that;
    }

    /**
     * @return static
     */
    public static function create(array $data): self
    {
        return static::make($data)->save();
    }

    /**
     * @return Collection<int, static>
     */
    protected static function fetchList(Builder $builder): Collection
    {
        $list = new Collection;

        foreach ($builder->fetchAll() as $data) {
            $that = static::make($data);
            $that->exists = true;
            $list[] = $that;
        }

        return $list;
    }

    /**
     * @return static
     */
    protected static function fetch(Builder $builder): self
    {
        $data = $builder->fetch();
        $that = static::make($data);
        $that->exists = true;

        return $that;
    }

    /**
     * @return Collection<int, static>
     */
    public static function all(): Collection
    {
        $builder = new Builder;

        $builder([
            'select' => '*',
            'from' => static::$table,
        ]);

        return static::fetchList($builder);
    }

    /**
     * @return Collection<int, static>
     */
    public static function where(mixed ...$arguments): Collection
    {
        $builder = new Builder;

        $builder([
            'select' => '*',
            'from' => static::$table,
            'where' => $arguments,
        ]);

        return static::fetchList($builder);
    }

    public static function find(mixed $id): self
    {
        $builder = new Builder;

        $builder([
            'select' => '*',
            'from' => static::$table,
            'where' => static::getPrimaryKey() . ' = ' . $id,
        ]);

        return static::fetch($builder);
    }

    public static function first(): self
    {
        $builder = new Builder;

        $builder([
            'select' => '*',
            'from' => static::$table,
            'limit' => 1,
        ]);

        return static::fetch($builder);
    }

    public static function last(): self
    {
        $builder = new Builder;

        $builder([
            'select' => '*',
            'from' => static::$table,
            'order' => 'id DESC',
            'limit' => 1,
        ]);

        return static::fetch($builder);
    }

    public function __get(string $key): mixed
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        throw new BadMethodCallException;
    }

    public function __set(string $key, mixed $value): void
    {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
            return;
        }

        throw new BadMethodCallException;
    }

    public function getChanges(): array
    {
        return array_diff_assoc($this->data, $this->temp);
    }

    public function save(): self
    {
        $builder = new Builder;

        if ($this->exists) {
            $builder([
                'update' => static::$table,
                'set' => implode(', ', array_map(function (string $key, mixed $value): string {
                    return $key . ' = ' . $value;
                }, $this->getChanges())),
                'where' => $this->getPrimaryKey() . ' = ' . $this->getPrimaryValue(),
            ]);
        } else {
            $builder([
                'insert into' => $this->table . ' (' . implode(', ', array_keys(static::$schema)) . ') ',
                'values' => '(' . implode(', ', array_fill(0, count(static::$schema), '?')) . ')',
                array_values($this->data)
            ]);
            $this->exists = true;
        }

        $this->temp = $this->data;

        return $this;
    }

    public function delete(): self
    {
        if ($this->exists) {
            $builder = new Builder;

            $builder([
                'delete from' => static::$table,
                'where' => $this->getPrimaryKey() . ' = ' . $this->getPrimaryValue(),
            ]);

            return $this;
        }

        throw new BadMethodCallException;
    }
}
