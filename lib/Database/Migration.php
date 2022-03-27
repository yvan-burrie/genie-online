<?php

namespace Lib\Database;

abstract class Migration
{
    protected Builder $builder;

    public function __construct()
    {
        $this->builder = new Builder;
    }

    abstract public function up(): bool;

    abstract public function down(): bool;
}
