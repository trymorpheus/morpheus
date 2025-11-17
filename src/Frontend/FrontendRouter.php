<?php

namespace Morpheus\Frontend;

/**
 * FrontendRouter
 * 
 * Routes public URLs to content (blog posts, pages, archives, etc.)
 */
class FrontendRouter
{
    private array $routes = [];
    
    public function __construct()
    {
        $this->registerDefaultRoutes();
    }
    
    /**
     * Add a route
     */
    public function addRoute(string $pattern, string $handler): void
    {
        $this->routes[$pattern] = $handler;
    }
    
    /**
     * Match URI to route
     */
    public function match(string $uri): ?Route
    {
        // Remove query string
        $uri = strtok($uri, '?');
        
        // Remove trailing slash
        $uri = rtrim($uri, '/');
        
        // Empty URI = home
        if ($uri === '') {
            $uri = '/';
        }
        
        foreach ($this->routes as $pattern => $handler) {
            $regex = $this->patternToRegex($pattern);
            
            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return new Route($pattern, $handler, $params);
            }
        }
        
        return null;
    }
    
    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * Convert route pattern to regex
     */
    private function patternToRegex(string $pattern): string
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Register default blog routes
     */
    private function registerDefaultRoutes(): void
    {
        // Home
        $this->addRoute('/', 'home');
        
        // Blog
        $this->addRoute('/blog', 'blog.archive');
        $this->addRoute('/blog/page/{page}', 'blog.archive');
        $this->addRoute('/blog/{slug}', 'blog.single');
        $this->addRoute('/blog/category/{slug}', 'blog.category');
        $this->addRoute('/blog/tag/{slug}', 'blog.tag');
        
        // Search
        $this->addRoute('/search', 'search');
        
        // Pages
        $this->addRoute('/{slug}', 'page.single');
    }
}
