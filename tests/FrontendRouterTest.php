<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\Frontend\FrontendRouter;

class FrontendRouterTest extends TestCase
{
    private FrontendRouter $router;
    
    protected function setUp(): void
    {
        $this->router = new FrontendRouter();
    }
    
    public function testMatchHome(): void
    {
        $route = $this->router->match('/');
        
        $this->assertNotNull($route);
        $this->assertEquals('/', $route->pattern);
        $this->assertEquals('home', $route->handler);
        $this->assertEmpty($route->params);
    }
    
    public function testMatchBlogArchive(): void
    {
        $route = $this->router->match('/blog');
        
        $this->assertNotNull($route);
        $this->assertEquals('/blog', $route->pattern);
        $this->assertEquals('blog.archive', $route->handler);
    }
    
    public function testMatchBlogSingle(): void
    {
        $route = $this->router->match('/blog/my-first-post');
        
        $this->assertNotNull($route);
        $this->assertEquals('/blog/{slug}', $route->pattern);
        $this->assertEquals('blog.single', $route->handler);
        $this->assertEquals('my-first-post', $route->params['slug']);
    }
    
    public function testMatchCategory(): void
    {
        $route = $this->router->match('/blog/category/technology');
        
        $this->assertNotNull($route);
        $this->assertEquals('/blog/category/{slug}', $route->pattern);
        $this->assertEquals('blog.category', $route->handler);
        $this->assertEquals('technology', $route->params['slug']);
    }
    
    public function testMatchTag(): void
    {
        $route = $this->router->match('/blog/tag/php');
        
        $this->assertNotNull($route);
        $this->assertEquals('/blog/tag/{slug}', $route->pattern);
        $this->assertEquals('blog.tag', $route->handler);
        $this->assertEquals('php', $route->params['slug']);
    }
    
    public function testMatchPagination(): void
    {
        $route = $this->router->match('/blog/page/2');
        
        $this->assertNotNull($route);
        $this->assertEquals('/blog/page/{page}', $route->pattern);
        $this->assertEquals('blog.archive', $route->handler);
        $this->assertEquals('2', $route->params['page']);
    }
    
    public function testMatchSearch(): void
    {
        $route = $this->router->match('/search');
        
        $this->assertNotNull($route);
        $this->assertEquals('/search', $route->pattern);
        $this->assertEquals('search', $route->handler);
    }
    
    public function testMatchPage(): void
    {
        $route = $this->router->match('/about');
        
        $this->assertNotNull($route);
        $this->assertEquals('/{slug}', $route->pattern);
        $this->assertEquals('page.single', $route->handler);
        $this->assertEquals('about', $route->params['slug']);
    }
    
    public function testMatchNonExistent(): void
    {
        $route = $this->router->match('/this/does/not/exist');
        
        $this->assertNull($route);
    }
    
    public function testAddCustomRoute(): void
    {
        $this->router->addRoute('/custom/{id}', 'custom.handler');
        $route = $this->router->match('/custom/123');
        
        $this->assertNotNull($route);
        $this->assertEquals('custom.handler', $route->handler);
        $this->assertEquals('123', $route->params['id']);
    }
    
    public function testGetRoutes(): void
    {
        $routes = $this->router->getRoutes();
        
        $this->assertIsArray($routes);
        $this->assertArrayHasKey('/', $routes);
        $this->assertArrayHasKey('/blog', $routes);
        $this->assertArrayHasKey('/blog/{slug}', $routes);
    }
    
    public function testTrailingSlashRemoved(): void
    {
        $route1 = $this->router->match('/blog/');
        $route2 = $this->router->match('/blog');
        
        $this->assertEquals($route1->handler, $route2->handler);
    }
}
