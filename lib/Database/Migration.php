<?php

namespace Lib\Database;

abstract class Migration
{
    abstract public function up(): bool;

    abstract public function down(): bool;
}
