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
        $user->role = 'admin'; // Forçar permissão de admin

        $this->info("Login como {$user->email} com role = admin...");
        Auth::login($user);

        $routesToTest = [
            'configuracoes',
            'configuracoes/role',
            'configuracoes/equipe',
            'configuracoes/equipe/create',
            'configuracoes/equipe/{equipe}',
            'configuracoes/equipe/{equipe}/edit',
            'configuracoes/movimento',
            'configuracoes/movimento/create',
            'configuracoes/movimento/{movimento}',
            'configuracoes/movimento/{movimento}/edit',
            'configuracoes/responsavel',
            'configuracoes/responsavel/create',
            'configuracoes/responsavel/{responsavel}',
            'configuracoes/responsavel/{responsavel}/edit',
            'configuracoes/restricao',
            'configuracoes/restricao/create',
            'configuracoes/restricao/{restricao}',
            'configuracoes/restricao/{restricao}/edit',
        ];

        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
        $rows = [];
        $markdown = "| Route (URI) | Tested URL | HTTP Status | Return Message |\n|-------------|------------|-------------|----------------|\n";

        foreach ($routesToTest as $uri) {
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
            
            $msg = str_replace(["\n", "\r"], " ", substr($msg, 0, 100)); // Limitar tamanho da mensagem
            
            $rows[] = ['Route' => $uri, 'URL' => $testUri, 'Status' => $status, 'Message' => $msg];
            $markdown .= "| {$uri} | {$testUri} | {$status} | {$msg} |\n";
        }
        
        $this->table(['Route (URI)', 'Tested URL', 'HTTP Status', 'Mensagem'], $rows);
        
        file_put_contents(base_path($this->option('report-file')), $markdown);
        $this->info("Relatório salvo em: " . base_path($this->option('report-file')));
        
        $user->forceDelete();
    }
}
