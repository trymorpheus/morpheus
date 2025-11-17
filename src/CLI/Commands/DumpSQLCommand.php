<?php

namespace Morpheus\CLI\Commands;

class DumpSQLCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            echo "❌ Error: Table name required\n";
            echo "Usage: php dynamiccrud dump:sql <table> [--output=file.sql] [--data-only] [--structure-only]\n";
            exit(1);
        }

        $table = $args[0];
        $output = $this->getOption($args, '--output');
        $dataOnly = in_array('--data-only', $args);
        $structureOnly = in_array('--structure-only', $args);

        try {
            $pdo = $this->getPDO();
            $dump = $this->generateDump($pdo, $table, $dataOnly, $structureOnly);

            if ($output) {
                file_put_contents($output, $dump);
                echo "✅ SQL dump saved to: $output\n";
            } else {
                echo $dump;
            }
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function generateDump(\PDO $pdo, string $table, bool $dataOnly, bool $structureOnly): string
    {
        $dump = "-- DynamicCRUD SQL Dump\n";
        $dump .= "-- Table: $table\n";
        $dump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        if (!$structureOnly) {
            $dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        }

        // Structure
        if (!$dataOnly) {
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $dump .= "DROP TABLE IF EXISTS `$table`;\n";
            $dump .= $row['Create Table'] . ";\n\n";
        }

        // Data
        if (!$structureOnly) {
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $dump .= "-- Data for table `$table`\n\n";
                
                foreach ($rows as $row) {
                    $columns = array_keys($row);
                    $values = array_map(function($val) use ($pdo) {
                        return $val === null ? 'NULL' : $pdo->quote($val);
                    }, array_values($row));

                    $dump .= sprintf(
                        "INSERT INTO `%s` (`%s`) VALUES (%s);\n",
                        $table,
                        implode('`, `', $columns),
                        implode(', ', $values)
                    );
                }
                $dump .= "\n";
            }
        }

        if (!$structureOnly) {
            $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        }

        return $dump;
    }
}
