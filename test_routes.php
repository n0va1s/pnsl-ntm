<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$consoleKernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$router = app('router');
$routes = $router->getRoutes();

echo "| Route (URI) | Tested URL | HTTP Status | Error details |\n";
echo "|-------------|------------|-------------|---------------|\n";

foreach ($routes as $route) {
    if (in_array('GET', $route->methods())) {
        $uri = $route->uri();
        
        if (strpos($uri, '_ignition') === 0) continue;
        if (strpos($uri, 'sanctum') === 0) continue;
        if (strpos($uri, 'livewire-e504497a') === 0) continue;
        if (strpos($uri, 'flux') === 0) continue;
        if (strpos($uri, 'up') === 0) continue;
        if (strpos($uri, '_debugbar') === 0) continue;
        
        $testUri = preg_replace('/\{[a-zA-Z0-9_\?\]\[\-]+\}/', '1', $uri);
        
        if ($testUri === '') $testUri = '/';
        elseif ($testUri[0] !== '/') $testUri = '/' . $testUri;
        
        $errorMsg = '';
        try {
            $request = Illuminate\Http\Request::create($testUri, 'GET');
            $response = $kernel->handle($request);
            $status = $response->getStatusCode();
        } catch (\Throwable $e) {
            $status = '500';
            $errorMsg = class_basename(get_class($e)) . ': ' . $e->getMessage();
        }
        
        echo "| " . rtrim($uri, '/') . " | $testUri | $status | $errorMsg |\n";
    }
}
