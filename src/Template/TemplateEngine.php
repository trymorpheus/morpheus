<?php

namespace Morpheus\Template;

interface TemplateEngine
{
    public function render(string $template, array $data = []): string;
    public function renderFile(string $path, array $data = []): string;
    public function exists(string $template): bool;
}
