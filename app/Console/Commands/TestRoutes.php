<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestRoutes extends Command
{
    protected $signature = 'route:test {--report-file=route_test_report.md}';
    protected $description = 'Test all GET routes and generate a report of HTTP status codes';

    public function handle()
    {
        $this->info('Criando usuário temporário para os testes (Admin)...');

        $user = clone User::firstOrCreate(
            ['email' => 'route_tester_admin@example.com'],
            [
                'name' => 'Route Tester',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        // Forçar permissão de admin (aplicado em runtime)
        $user->role = 'admin'; 

        $this->info("Login como {$user->email} com role = admin...");
        Auth::login($user);

        $routes = app('router')->getRoutes();
        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
        $rows = [];
        $markdown = "| Route (URI) | Tested URL | HTTP Status | Return Message |\n|-------------|------------|-------------|----------------|\n";

        foreach ($routes as $route) {
            if (in_array('GET', $route->methods())) {
                $uri = $route->uri();
                
                // Rotas a ignorar
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
                
                // Configura Mock Parameters
                $testUri = preg_replace('/\{[a-zA-Z0-9_\?\]\[\-]+\}/', '1', $uri);
                if ($testUri === '') $testUri = '/';
                elseif ($testUri[0] !== '/') $testUri = '/' . $testUri;
                
                $msg = '';
                try {
                    $request = Request::create($testUri, 'GET');
                    $request->setUserResolver(fn () => clone $user);
                    
                    $response = $kernel->handle($request);
                    $status = $response->getStatusCode();
                    
                    if (isset($response->exception) && $response->exception) {
                        $msg = $response->exception->getMessage();
                        if (empty($msg)) {
                            $msg = class_basename(get_class($response->exception));
                        }
                    } else {
                        $msg = 'OK';
                    }
                } catch (\Throwable $e) {
                    $status = '500';
                    $msg = $e->getMessage();
                    if (empty($msg)) {
                        $msg = class_basename(get_class($e));
                    }
                }
                
                // Limpar texto de mensagem para exibir na tabela
                $msg = str_replace(["\n", "\r"], " ", substr($msg, 0, 100));
                
                $rows[] = ['Route' => $uri, 'Tested URL' => $testUri, 'Status' => $status, 'Message' => $msg];
                $markdown .= "| {$uri} | {$testUri} | {$status} | {$msg} |\n";
            }
        }
        
        $this->table(['Route (URI)', 'Tested URL', 'HTTP Status', 'Mensagem'], $rows);
        
        file_put_contents(base_path($this->option('report-file')), $markdown);
        $this->info("Relatório salvo em: " . base_path($this->option('report-file')));
        
        $user->forceDelete();
    }
}
