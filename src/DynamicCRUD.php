<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\Cache\CacheStrategy;

class DynamicCRUD
{
    private CRUDHandler $handler;

    public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null)
    {
        $this->handler = new CRUDHandler($pdo, $table, $cache);
    }

    public function renderForm(?int $id = null): string
    {
        return $this->handler->renderForm($id);
    }

    public function handleSubmission(): array
    {
        return $this->handler->handleSubmission();
    }

    public function list(array $options = []): array
    {
        return $this->handler->list($options);
    }

    public function delete(int $id): bool
    {
        return $this->handler->delete($id);
    }
}
