<?php

namespace Lib\Database;

use DirectoryIterator;
use Generator;
use PDOException;
use ReflectionClass;
use UnexpectedValueException;

abstract class Migrator
{
    protected static string $table = 'migrations';

    protected static Builder $builder;

    public static function boot(): void
    {
        static::$builder = new Builder;
    }

    public static function manifest(): void
    {
        $builder = static::$builder;

        $builder([
            'create table if not exists' => '`' . static::$table . '`',
            '(',
            '`id`' => 'INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)',
            ',',
            '`name`' => 'varchar(191) NOT NULL',
            ',',
            '`done`' => 'tinyint(1) NOT NULL',
            ')',
        ])->execute();

        $builder([
            'alter table' => '`' . static::$table . '`',
            'add unique key' => '`name_index` (`name`);',
            'commit;',
        ])->execute();
    }

    public static function getExistingMigrations(): array
    {
        $builder = static::$builder;

        return $builder([
            'select * from' => '`' . static::$table . '`',
        ])->fetchAll();
    }

    public static function getPotentialMigrationNames(): array
    {
        $potentialMigrationNames = [];

        $files = new DirectoryIterator(__DIR__ . '/../../app/Migrations');
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }
            $potentialMigrationName = $file->getBasename('.php');
            $potentialMigrationNames[] = $potentialMigrationName;
        }

        return $potentialMigrationNames;
    }

    public static function run(array $options = []): Generator
    {
        $builder = static::$builder;

        try {
            $existingMigrations = Migrator::getExistingMigrations();
        } catch (PDOException $exception) {
            Migrator::manifest();
            $existingMigrations = Migrator::getExistingMigrations();
        }

        $potentialMigrationNames = Migrator::getPotentialMigrationNames();
        if ($down = $options['down'] ?? false) {
            $potentialMigrationNames = array_reverse($potentialMigrationNames);
        }
        $step = $options['steps'] ?? 999999;
        foreach ($potentialMigrationNames as $i => $potentialMigrationName) {
            if ($step-- < 1) {
                break;
            }
            $existingMigration = &$existingMigrations[$i];
            if ($existingMigration === null) {
                $existingMigration = [
                    'name' => $potentialMigrationName,
                    'done' => 0,
                ];
                $builder([
                    'insert into' => '`' . static::$table . '`',
                    '(`name`, `done`)',
                    'values' => '("' . $potentialMigrationName . '", 0)',
                ])->execute();
                $migrationNamePieces = explode('_', $potentialMigrationName);
                $migration = static::runMigration($migrationNamePieces, $down);
                yield $migration;
                continue;
            }
            if ($existingMigration['name'] !== $potentialMigrationName) {
                throw new UnexpectedValueException;
            }
            if ($existingMigration['done'] === 0) {
                $migrationNamePieces = explode('_', $potentialMigrationName);
                $migration = static::runMigration($migrationNamePieces, $down);
                yield $migration;
                continue;
            }
        }
    }

    /**
     * @param string[] $namePieces
     * @param bool $down
     * @return Migration
     */
    public static function runMigration(array $namePieces, bool $down = false): Migration
    {
        $builder = static::$builder;

        $migrationClass = '\App\Migrations\\' . $namePieces[1];
        $reflection = new ReflectionClass($migrationClass);

        /** @var Migration $migration */
        $migration = $reflection->newInstanceWithoutConstructor();

        if ($down) {
            $migration->down();
        } else {
            $migration->up();
        }

        $builder([
            'update' => '`' . static::$table . '`',
            'set' => '`done` = ' . ($down ? 0 : 1),
            'where' => '`name` = "' . implode('_', $namePieces) . '"',
        ])->execute();

        return $migration;
    }
}


