<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkipDatabaseCheck
{
    public function handle(Request $request, Closure $next)
    {
        // Configura SQLite en memoria
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        
        return $next($request);
    }
}
