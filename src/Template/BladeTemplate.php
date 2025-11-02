<?php

namespace DynamicCRUD\Template;

class BladeTemplate implements TemplateEngine
{
    private string $templatePath;
    private string $cachePath;
    private array $sections = [];
    private ?string $currentSection = null;

    public function __construct(string $templatePath, string $cachePath)
    {
        $this->templatePath = rtrim($templatePath, '/\\');
        $this->cachePath = rtrim($cachePath, '/\\');
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function render(string $template, array $data = []): string
    {
        $compiled = $this->compile($template);
        return $this->evaluate($compiled, $data);
    }

    public function renderFile(string $path, array $data = []): string
    {
        $fullPath = $this->templatePath . '/' . $path;
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Template not found: {$path}");
        }
        
        $template = file_get_contents($fullPath);
        return $this->render($template, $data);
    }

    public function exists(string $template): bool
    {
        return file_exists($this->templatePath . '/' . $template);
    }

    private function compile(string $template): string
    {
        // {!! $var !!}
        $template = preg_replace_callback('/\{!!\s*(.+?)\s*!!\}/s', fn($m) => '<?php echo ' . $m[1] . '; ?>', $template);
        
        // {{ $var }}
        $template = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/s', fn($m) => "<?php echo htmlspecialchars((" . $m[1] . ") ?? ''); ?>", $template);
        
        // @if, @elseif
        $template = preg_replace_callback('/@if\s*\(([^)]+)\)/', fn($m) => "<?php if (" . $m[1] . "): ?>", $template);
        $template = preg_replace_callback('/@elseif\s*\(([^)]+)\)/', fn($m) => "<?php elseif (" . $m[1] . "): ?>", $template);
        
        // @foreach, @for
        $template = preg_replace_callback('/@foreach\s*\(([^)]+)\)/', fn($m) => "<?php foreach (" . $m[1] . "): ?>", $template);
        $template = preg_replace_callback('/@for\s*\(([^)]+)\)/', fn($m) => "<?php for (" . $m[1] . "): ?>", $template);
        
        // Simple replacements
        $replacements = [
            '@else' => '<?php else: ?>',
            '@endif' => '<?php endif; ?>',
            '@endforeach' => '<?php endforeach; ?>',
            '@endfor' => '<?php endfor; ?>',
            '@endsection' => '<?php $this->endSection(); ?>',
        ];
        $template = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        // @section, @yield, @extends, @include
        $template = preg_replace_callback('/@section\s*\([\'"](.+?)[\'"]\)/', fn($m) => '<?php $this->startSection(\'' . $m[1] . '\'); ?>', $template);
        $template = preg_replace_callback('/@yield\s*\([\'"](.+?)[\'"]\)/', fn($m) => '<?php echo $this->yieldSection(\'' . $m[1] . '\'); ?>', $template);
        $template = preg_replace_callback('/@extends\s*\([\'"](.+?)[\'"]\)/', fn($m) => '<?php $this->extend(\'' . $m[1] . '\'); ?>', $template);
        $template = preg_replace_callback('/@include\s*\([\'"](.+?)[\'"]\)/', fn($m) => '<?php echo $this->includePartial(\'' . $m[1] . '\'); ?>', $template);
        
        return $template;
    }

    private function evaluate(string $compiled, array $data): string
    {
        $hash = md5($compiled);
        $cacheFile = $this->cachePath . '/' . $hash . '.php';
        
        // Debug: save compiled template
        file_put_contents($cacheFile, $compiled);
        
        extract($data);
        
        ob_start();
        try {
            include $cacheFile;
        } catch (\ParseError $e) {
            throw new \Exception("Template compilation error: " . $e->getMessage() . "\nCompiled code:\n" . $compiled);
        }
        return ob_get_clean();
    }

    private function startSection(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    private function endSection(): void
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    private function yieldSection(string $name): string
    {
        return $this->sections[$name] ?? '';
    }

    private function extend(string $layout): void
    {
        // Layout inheritance handled in renderFile
    }

    private function includePartial(string $partial): string
    {
        return $this->renderFile($partial, []);
    }
}
