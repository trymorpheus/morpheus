<?php

namespace Morpheus\CLI\Commands;

use PDO;
use Morpheus\DynamicCRUD;

class GenerateTemplateCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error('Table name required');
            $this->info('Usage: php dynamiccrud generate:template <table> [--output=template.csv]');
            exit(1);
        }

        $table = $args[0];
        $output = $this->getOption($args, '--output');
        $pdo = $this->getPDO();

        $crud = new Morpheus($pdo, $table);
        $template = $crud->generateImportTemplate();

        if ($output) {
            file_put_contents($output, $template);
            $this->success("Template generated: $output");
        } else {
            echo $template;
        }
    }

    protected function getOption(array $args, string $name): ?string
    {
        foreach ($args as $arg) {
            if (strpos($arg, $name . '=') === 0) {
                return substr($arg, strlen($name) + 1);
            }
        }
        return null;
    }
}
