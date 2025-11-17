<?php

namespace Morpheus\Theme;

interface Theme
{
    public function getName(): string;
    public function getDescription(): string;
    public function getVersion(): string;
    public function getAuthor(): string;
    public function getScreenshot(): string;
    
    public function getConfig(): array;
    public function getTemplates(): array;
    public function getAssets(): array;
    
    public function render(string $template, array $data): string;
}
