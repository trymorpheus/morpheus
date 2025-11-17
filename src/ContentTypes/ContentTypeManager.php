<?php

namespace Morpheus\ContentTypes;

/**
 * ContentTypeManager
 * 
 * Manages installation and lifecycle of content types
 */
class ContentTypeManager
{
    private \PDO $pdo;
    private array $contentTypes = [];
    
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->registerDefaultContentTypes();
    }
    
    /**
     * Register a content type
     */
    public function register(ContentType $contentType): void
    {
        $this->contentTypes[$contentType->getName()] = $contentType;
    }
    
    /**
     * Install a content type
     */
    public function install(string $name): bool
    {
        if (!isset($this->contentTypes[$name])) {
            throw new \Exception("Content type '{$name}' not found");
        }
        
        return $this->contentTypes[$name]->install($this->pdo);
    }
    
    /**
     * Uninstall a content type
     */
    public function uninstall(string $name): bool
    {
        if (!isset($this->contentTypes[$name])) {
            throw new \Exception("Content type '{$name}' not found");
        }
        
        return $this->contentTypes[$name]->uninstall($this->pdo);
    }
    
    /**
     * Get all available content types
     */
    public function getAvailable(): array
    {
        return array_map(fn($ct) => [
            'name' => $ct->getName(),
            'description' => $ct->getDescription(),
            'installed' => $ct->isInstalled($this->pdo)
        ], $this->contentTypes);
    }
    
    /**
     * Get installed content types
     */
    public function getInstalled(): array
    {
        return array_filter(
            $this->getAvailable(),
            fn($ct) => $ct['installed']
        );
    }
    
    /**
     * Check if content type is installed
     */
    public function isInstalled(string $name): bool
    {
        if (!isset($this->contentTypes[$name])) {
            return false;
        }
        
        return $this->contentTypes[$name]->isInstalled($this->pdo);
    }
    
    /**
     * Register default content types
     */
    private function registerDefaultContentTypes(): void
    {
        $this->register(new BlogContentType());
        // Portfolio, Ecommerce, Directory will be added in next iterations
    }
}
