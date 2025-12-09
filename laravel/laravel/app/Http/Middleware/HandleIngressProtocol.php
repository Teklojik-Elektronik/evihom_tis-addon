<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class HandleIngressProtocol
{
    /**
     * Handle ingress protocol switching between HTTP and HTTPS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log request details for debugging
        Log::info('HandleIngressProtocol middleware called', [
            'host' => $request->getHost(),
            'uri' => $request->getRequestUri(),
            'referer' => $request->header('referer'),
            'x-forwarded-proto' => $request->header('x-forwarded-proto'),
            'HTTP_X_INGRESS_PATH' => $request->server->get('HTTP_X_INGRESS_PATH'),
            'INGRESS_PATH' => env('INGRESS_PATH')
        ]);

        // Fix for mixed content issues with Cloudflare tunnel
        // Check headers that might indicate we're behind a secure proxy
        $secureHeaders = [
            'x-forwarded-proto' => 'https',
            'x-forwarded-ssl' => 'on',
            'front-end-https' => 'on'
        ];

        foreach ($secureHeaders as $header => $value) {
            if ($request->header($header) == $value) {
                $request->server->set('HTTPS', 'on');
                break;
            }
        }

        // Check if we're accessing through the Cloudflare tunnel
        if (str_contains($request->getHost(), '.tis-homeassistant.com')) {
            $request->server->set('HTTPS', 'on');
        }

        // Handle Home Assistant ingress paths
        $ingressPath = $request->server->get('HTTP_X_INGRESS_PATH', env('INGRESS_PATH', ''));
        if ($ingressPath && !str_starts_with($request->getPathInfo(), $ingressPath)) {
            // Adjust for any path handling that might be needed for ingress
            Log::info('Adjusting for ingress path', [
                'ingressPath' => $ingressPath,
                'pathInfo' => $request->getPathInfo()
            ]);
        }

        return $next($request);
    }
}
