<?php

use PHPUnit\Framework\TestCase;
use DynamicCRUD\I18n\Translator;

class TranslatorTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear session before each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
        $_GET = [];
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
    }

    public function testConstructorWithExplicitLocale()
    {
        $translator = new Translator('es');
        $this->assertEquals('es', $translator->getLocale());
    }

    public function testConstructorWithDefaultLocale()
    {
        $translator = new Translator();
        $this->assertEquals('en', $translator->getLocale());
    }

    public function testDetectLocaleFromUrlParameter()
    {
        $_GET['lang'] = 'fr';
        $translator = new Translator();
        $this->assertEquals('fr', $translator->getLocale());
    }

    public function testDetectLocaleFromSession()
    {
        $_SESSION['locale'] = 'es';
        $translator = new Translator();
        $this->assertEquals('es', $translator->getLocale());
    }

    public function testDetectLocaleFromBrowserHeader()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR,fr;q=0.9,en;q=0.8';
        $translator = new Translator();
        $this->assertEquals('fr', $translator->getLocale());
    }

    public function testUrlParameterTakesPrecedenceOverSession()
    {
        $_SESSION['locale'] = 'es';
        $_GET['lang'] = 'fr';
        $translator = new Translator();
        $this->assertEquals('fr', $translator->getLocale());
    }

    public function testSessionTakesPrecedenceOverBrowser()
    {
        $_SESSION['locale'] = 'es';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR,fr;q=0.9';
        $translator = new Translator();
        $this->assertEquals('es', $translator->getLocale());
    }

    public function testTranslateSimpleKey()
    {
        $translator = new Translator('en');
        $result = $translator->t('form.submit');
        $this->assertEquals('Submit', $result);
    }

    public function testTranslateKeyWithParameters()
    {
        $translator = new Translator('en');
        $result = $translator->t('validation.required', ['field' => 'email']);
        $this->assertEquals('The email field is required', $result);
    }

    public function testTranslateKeyWithMultipleParameters()
    {
        $translator = new Translator('en');
        $result = $translator->t('validation.min', ['field' => 'age', 'min' => 18]);
        $this->assertEquals('The age field must be at least 18', $result);
    }

    public function testTranslateNonexistentKeyReturnsKey()
    {
        $translator = new Translator('en');
        $result = $translator->t('nonexistent.key');
        $this->assertEquals('nonexistent.key', $result);
    }

    public function testTranslateInSpanish()
    {
        $translator = new Translator('es');
        $result = $translator->t('form.submit');
        $this->assertEquals('Enviar', $result);
    }

    public function testTranslateInFrench()
    {
        $translator = new Translator('fr');
        $result = $translator->t('form.submit');
        $this->assertEquals('Soumettre', $result);
    }

    public function testSpanishValidationMessage()
    {
        $translator = new Translator('es');
        $result = $translator->t('validation.email', ['field' => 'correo']);
        $this->assertEquals('El campo correo debe ser un email válido', $result);
    }

    public function testFrenchValidationMessage()
    {
        $translator = new Translator('fr');
        $result = $translator->t('validation.email', ['field' => 'email']);
        $this->assertEquals('Le champ email doit être un email valide', $result);
    }

    public function testInvalidLocaleDefaultsToEnglish()
    {
        $translator = new Translator('invalid');
        $this->assertEquals('en', $translator->getLocale());
    }

    public function testGetAllTranslations()
    {
        $translator = new Translator('en');
        $translations = $translator->getAllTranslations();
        
        $this->assertIsArray($translations);
        $this->assertArrayHasKey('form.submit', $translations);
        $this->assertArrayHasKey('validation.required', $translations);
    }

    public function testParameterReplacementWithMissingParameter()
    {
        $translator = new Translator('en');
        $result = $translator->t('validation.required', []); // Missing 'field' parameter
        $this->assertEquals('The {field} field is required', $result);
    }

    public function testParameterReplacementWithExtraParameters()
    {
        $translator = new Translator('en');
        $result = $translator->t('form.submit', ['extra' => 'value']);
        $this->assertEquals('Submit', $result); // Extra parameters ignored
    }

    public function testCommonTranslations()
    {
        $translator = new Translator('en');
        
        $this->assertEquals('Yes', $translator->t('common.yes'));
        $this->assertEquals('No', $translator->t('common.no'));
        $this->assertEquals('Save', $translator->t('common.save'));
        $this->assertEquals('Delete', $translator->t('common.delete'));
    }

    public function testErrorMessages()
    {
        $translator = new Translator('en');
        
        $this->assertEquals('Invalid CSRF token', $translator->t('error.csrf_invalid'));
        $this->assertEquals('Validation failed', $translator->t('error.validation_failed'));
    }

    public function testSuccessMessages()
    {
        $translator = new Translator('en');
        
        $this->assertEquals('Record created successfully', $translator->t('success.created'));
        $this->assertEquals('Record updated successfully', $translator->t('success.updated'));
        $this->assertEquals('Record deleted successfully', $translator->t('success.deleted'));
    }

    public function testManyToManyTranslations()
    {
        $translator = new Translator('en');
        
        $result = $translator->t('m2m.selected', ['count' => 5]);
        $this->assertEquals('5 selected', $result);
        
        $this->assertEquals('No results found', $translator->t('m2m.no_results'));
    }

    public function testBrowserLanguageParsingWithQuality()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-ES,es;q=0.9,en-US;q=0.8,en;q=0.7';
        $translator = new Translator();
        $this->assertEquals('es', $translator->getLocale());
    }

    public function testBrowserLanguageParsingWithRegion()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
        $translator = new Translator();
        $this->assertEquals('en', $translator->getLocale());
    }

    public function testBrowserLanguageParsingWithUnsupportedLanguage()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.9';
        $translator = new Translator();
        $this->assertEquals('en', $translator->getLocale()); // Falls back to default
    }

    public function testSetLocale()
    {
        $translator = new Translator('en');
        $this->assertEquals('en', $translator->getLocale());
        
        $translator->setLocale('es');
        $this->assertEquals('es', $translator->getLocale());
        
        $result = $translator->t('form.submit');
        $this->assertEquals('Enviar', $result);
    }

    public function testSetInvalidLocaleFallsBackToEnglish()
    {
        $translator = new Translator('en');
        $translator->setLocale('invalid');
        $this->assertEquals('en', $translator->getLocale());
    }

    public function testTranslationConsistencyAcrossLanguages()
    {
        $en = new Translator('en');
        $es = new Translator('es');
        $fr = new Translator('fr');
        
        // All languages should have the same keys
        $enKeys = array_keys($en->getAllTranslations());
        $esKeys = array_keys($es->getAllTranslations());
        $frKeys = array_keys($fr->getAllTranslations());
        
        $this->assertEquals($enKeys, $esKeys);
        $this->assertEquals($enKeys, $frKeys);
    }

    public function testNumericParameterReplacement()
    {
        $translator = new Translator('en');
        $result = $translator->t('validation.maxlength', ['field' => 'name', 'maxlength' => 255]);
        $this->assertEquals('The name field cannot exceed 255 characters', $result);
    }

    public function testCaseInsensitiveParameterReplacement()
    {
        $translator = new Translator('en');
        // Parameters should be case-sensitive
        $result = $translator->t('validation.required', ['Field' => 'email']); // Wrong case
        $this->assertEquals('The {field} field is required', $result); // Not replaced
    }
}
