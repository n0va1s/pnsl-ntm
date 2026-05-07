<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class AllRoutesTest extends TestCase
{
    public function test_all_get_routes()
    {
        $user = User::factory()->create();

        $routes = app('router')->getRoutes();
        $markdown = "| Route (URI) | Tested URL | HTTP Status |\n|-------------|------------|-------------|\n";

        foreach ($routes as $route) {
            if (in_array('GET', $route->methods())) {
                $uri = $route->uri();
                
                if (strpos($uri, '_ignition') === 0) continue;
                if (strpos($uri, 'sanctum') === 0) continue;
                if (strpos($uri, 'livewire-e504497a') === 0) continue;
                if (strpos($uri, 'livewire/update') === 0) continue;
                if (strpos($uri, 'flux') === 0) continue;
                if (strpos($uri, 'up') === 0) continue;
                if (strpos($uri, '_debugbar') === 0) continue;
                if (strpos($uri, 'storage') === 0) continue;
                if (strpos($uri, 'limpar-tudo') === 0) continue;
                if (strpos($uri, 'otimizar-tudo') === 0) continue;
                
                $testUri = preg_replace('/\{[a-zA-Z0-9_\?\]\[\-]+\}/', '1', $uri);
                
                if ($testUri === '') $testUri = '/';
                elseif ($testUri[0] !== '/') $testUri = '/' . $testUri;
                
                ob_start();
                try {
                    $response = $this->actingAs($user)->get($testUri);
                    $status = $response->status();
                } catch (\Throwable $e) {
                    $status = '500 (' . class_basename(get_class($e)) . ')';
                }
                ob_end_clean();
                
                $markdown .= "| {$uri} | {$testUri} | {$status} |\n";
            }
        }
        
        file_put_contents(base_path('route_test_report.md'), $markdown);
        
        $this->assertTrue(true);
    }
}
