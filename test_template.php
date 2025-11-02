<?php

require 'vendor/autoload.php';

use DynamicCRUD\Template\BladeTemplate;

$engine = new BladeTemplate(__DIR__ . '/templates', __DIR__ . '/cache/templates');

$template = 'Hello, {{ $name }}!';

// Test compilation
$reflection = new ReflectionClass($engine);
$method = $reflection->getMethod('compile');
$method->setAccessible(true);

$compiled = $method->invoke($engine, $template);

echo "Original:\n";
echo $template . "\n\n";

echo "Compiled:\n";
echo $compiled . "\n\n";

echo "Result:\n";
try {
    echo $engine->render($template, ['name' => 'World']);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
