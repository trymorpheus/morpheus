<?php

namespace Morpheus\Tests;

use PDO;

class TestHelper
{
    public static function getPDO(): PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: 3306;
        $dbname = getenv('DB_NAME') ?: 'test';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: 'rootpassword';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function getPostgresPDO(): PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PG_PORT') ?: 5432;
        $dbname = getenv('DB_NAME') ?: 'test';
        $user = getenv('DB_USER') ?: 'postgres';
        $pass = getenv('DB_PASS') ?: 'postgres';

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
