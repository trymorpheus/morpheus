<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\Cache\FileCacheStrategy;

$cache = new FileCacheStrategy();
$cache->clear();

echo "✓ Caché limpiada exitosamente\n";
echo "Ahora recarga los formularios para ver los cambios.\n";
