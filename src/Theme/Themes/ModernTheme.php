<?php

namespace Morpheus\Theme\Themes;

use Morpheus\Theme\AbstractTheme;

class ModernTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'Modern';
    }
    
    public function getDescription(): string
    {
        return 'Modern theme with gradients, animations, and dark mode support.';
    }
}
