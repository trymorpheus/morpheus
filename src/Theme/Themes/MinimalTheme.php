<?php

namespace Morpheus\Theme\Themes;

use Morpheus\Theme\AbstractTheme;

class MinimalTheme extends AbstractTheme
{
    public function getName(): string
    {
        return 'Minimal';
    }
    
    public function getDescription(): string
    {
        return 'Clean, simple design focused on content. Fast loading and mobile-first.';
    }
}
