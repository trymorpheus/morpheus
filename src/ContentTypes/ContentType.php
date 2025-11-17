<?php

namespace Morpheus\ContentTypes;

/**
 * ContentType Interface
 * 
 * Defines the contract for all content types (Blog, Portfolio, E-commerce, Directory)
 */
interface ContentType
{
    /**
     * Get content type name
     */
    public function getName(): string;
    
    /**
     * Get content type description
     */
    public function getDescription(): string;
    
    /**
     * Get SQL statements to create tables
     */
    public function getTables(): array;
    
    /**
     * Get table metadata (JSON for COMMENT fields)
     */
    public function getMetadata(): array;
    
    /**
     * Install content type (create tables, insert metadata)
     */
    public function install(\PDO $pdo): bool;
    
    /**
     * Uninstall content type (drop tables)
     */
    public function uninstall(\PDO $pdo): bool;
    
    /**
     * Get sample data for demo purposes
     */
    public function getSampleData(): array;
    
    /**
     * Check if content type is installed
     */
    public function isInstalled(\PDO $pdo): bool;
}
