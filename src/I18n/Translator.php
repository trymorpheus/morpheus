<?php

namespace Morpheus\I18n;

class Translator
{
    private string $locale;
    private array $translations = [];
    private string $fallbackLocale = 'en';

    public function __construct(?string $locale = null)
    {
        $this->locale = $locale ?? self::detectLocale();
        $this->loadTranslations($this->locale);
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        $this->loadTranslations($locale);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function translate(string $key, array $params = []): string
    {
        $translation = $this->translations[$key] ?? $key;
        
        // Replace parameters {field}, {min}, {max}, etc.
        foreach ($params as $param => $value) {
            $translation = str_replace("{{$param}}", $value, $translation);
        }
        
        return $translation;
    }

    public function t(string $key, array $params = []): string
    {
        return $this->translate($key, $params);
    }

    public function getAllTranslations(): array
    {
        return $this->translations;
    }

    private function loadTranslations(string $locale): void
    {
        $file = __DIR__ . "/locales/{$locale}.php";
        
        if (file_exists($file)) {
            $this->translations = require $file;
            $this->locale = $locale;
        } elseif ($locale !== $this->fallbackLocale) {
            // Load fallback
            $fallbackFile = __DIR__ . "/locales/{$this->fallbackLocale}.php";
            if (file_exists($fallbackFile)) {
                $this->translations = require $fallbackFile;
                $this->locale = $this->fallbackLocale;
            }
        }
    }

    public static function detectLocale(): string
    {
        // Check URL parameter
        if (isset($_GET['lang'])) {
            return $_GET['lang'];
        }
        
        // Check session
        if (isset($_SESSION['locale'])) {
            return $_SESSION['locale'];
        }
        
        // Check browser language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            return $lang;
        }
        
        return 'en';
    }
}
