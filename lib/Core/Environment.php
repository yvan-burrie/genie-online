<?php

namespace Lib\Core;

class Environment
{
    public static self $singleton;

    private array $data = [];

    public function __construct()
    {
        $this->data = parse_ini_file(__DIR__ . '../../.env');

        static::$singleton = $this;
    }

    public function __get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }
}

new Environment;

function env(string $key = null)
{
    return $key === null
        ? Environment::$singleton
        : Environment::$singleton->{$key};
}
