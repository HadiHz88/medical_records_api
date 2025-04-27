<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTemplateAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $templateId = $request->route('template');
        
        if (!$request->user() || !$request->user()->hasPermissionTo($templateId)) {
            return response()->json(['message' => 'Unauthorized. You do not have access to this template.'], 403);
        }

        return $next($request);
    }
} 