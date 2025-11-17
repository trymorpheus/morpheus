<?php

namespace Morpheus\Database;

interface DatabaseAdapter
{
    public function getTableSchema(string $table): array;
    public function getForeignKeys(string $table): array;
    public function getEnumValues(string $table, string $column): array;
    public function quote(string $identifier): string;
    public function getLastInsertId(): int;
}
