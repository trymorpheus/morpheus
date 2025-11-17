<?php

use PHPUnit\Framework\TestCase;
use Morpheus\VirtualField;

class VirtualFieldTest extends TestCase
{
    public function testVirtualFieldCreation()
    {
        $field = new VirtualField('test_field', 'text', 'Test Field');
        
        $this->assertEquals('test_field', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertEquals('Test Field', $field->getLabel());
        $this->assertFalse($field->isRequired());
    }
    
    public function testVirtualFieldWithDefaultLabel()
    {
        $field = new VirtualField('user_name');
        
        $this->assertEquals('User name', $field->getLabel());
    }
    
    public function testRequiredFieldValidation()
    {
        $field = new VirtualField('email', 'email', 'Email', true);
        
        $this->assertFalse($field->validate('', []));
        $this->assertTrue($field->validate('test@example.com', []));
    }
    
    public function testCustomValidator()
    {
        $field = new VirtualField(
            'password_confirmation',
            'password',
            'Confirm Password',
            true,
            function($value, $allData) {
                return isset($allData['password']) && $value === $allData['password'];
            }
        );
        
        // Passwords match
        $this->assertTrue($field->validate('secret123', ['password' => 'secret123']));
        
        // Passwords don't match
        $this->assertFalse($field->validate('different', ['password' => 'secret123']));
        
        // Password field missing
        $this->assertFalse($field->validate('secret123', []));
    }
    
    public function testCheckboxValidator()
    {
        $field = new VirtualField(
            'terms_accepted',
            'checkbox',
            'Accept Terms',
            true,
            function($value, $allData) {
                return $value === '1';
            }
        );
        
        $this->assertTrue($field->validate('1', []));
        $this->assertFalse($field->validate('0', []));
        $this->assertFalse($field->validate('', []));
    }
    
    public function testAttributes()
    {
        $field = new VirtualField(
            'email',
            'email',
            'Email',
            false,
            null,
            [
                'placeholder' => 'Enter your email',
                'tooltip' => 'We will never share your email',
                'minlength' => 5
            ]
        );
        
        $attributes = $field->getAttributes();
        
        $this->assertEquals('Enter your email', $attributes['placeholder']);
        $this->assertEquals('We will never share your email', $attributes['tooltip']);
        $this->assertEquals(5, $attributes['minlength']);
    }
    
    public function testCustomErrorMessage()
    {
        $field = new VirtualField(
            'password_confirmation',
            'password',
            'Confirm Password',
            true,
            null,
            ['error_message' => 'Passwords do not match']
        );
        
        $this->assertEquals('Passwords do not match', $field->getErrorMessage());
    }
    
    public function testDefaultErrorMessage()
    {
        $field = new VirtualField('test_field', 'text', 'Test Field');
        
        $this->assertEquals('El campo Test Field no es válido', $field->getErrorMessage());
    }
    
    public function testOptionalFieldWithValidator()
    {
        $field = new VirtualField(
            'optional_field',
            'text',
            'Optional',
            false,
            function($value, $allData) {
                return strlen($value) >= 3;
            }
        );
        
        // Empty value is valid for optional field
        $this->assertTrue($field->validate('', []));
        
        // Non-empty value must pass validator
        $this->assertTrue($field->validate('abc', []));
        $this->assertFalse($field->validate('ab', []));
    }
}
