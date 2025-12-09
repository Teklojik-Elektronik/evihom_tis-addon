<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Log for debugging
        Log::info('AppServiceProvider register called', [
            'INGRESS_URL' => env('INGRESS_URL'),
            'SCRIPT_NAME' => $this->app['request']->server->get('SCRIPT_NAME', 'not set'),
            'HTTP_X_INGRESS_PATH' => $this->app['request']->server->get('HTTP_X_INGRESS_PATH', 'not set'),
            'REQUEST_URI' => $this->app['request']->server->get('REQUEST_URI', 'not set')
        ]);

        // Handle the Home Assistant ingress prefix if present
        if (env('INGRESS_URL')) {
            $ingressPath = parse_url(env('INGRESS_URL'), PHP_URL_PATH);
            Log::info("AppServiceProvider register called ingress path = " . $ingressPath);
            if ($ingressPath) {
                $this->app['request']->server->set('SCRIPT_NAME', $ingressPath);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Detect if we need to use HTTPS
        $isSecure = request()->secure() ||
                    request()->header('x-forwarded-proto') == 'https' ||
                    str_contains(env('INGRESS_URL', ''), 'https');

        // Log for debugging
        Log::info('AppServiceProvider boot called', [
            'isSecure' => $isSecure,
            'request()->secure()' => request()->secure(),
            'x-forwarded-proto' => request()->header('x-forwarded-proto'),
            'REQUEST_URI' => request()->server->get('REQUEST_URI'),
            'INGRESS_URL' => env('INGRESS_URL'),
            'HTTP_HOST' => request()->server->get('HTTP_HOST'),
            'HTTP_X_INGRESS_PATH' => request()->server->get('HTTP_X_INGRESS_PATH')
        ]);

        if ($isSecure) {
            URL::forceScheme('https');
        } else {
            URL::forceScheme('http');
        }

        // Handle Home Assistant ingress path
        if (request()->server->has('HTTP_X_INGRESS_PATH')) {
            // Force Laravel to use the ingress base path
            $ingressPath = request()->server->get('HTTP_X_INGRESS_PATH');
            $host = request()->getHost();
            $url = ($isSecure ? 'https://' : 'http://') . $host . $ingressPath;
            Log::info('Using X_INGRESS_PATH', ['url' => $url]);
            URL::forceRootUrl($url);
        } else if (env('INGRESS_URL')) {
            // Use the ingress URL as is
            Log::info('Using INGRESS_URL', ['url' => env('INGRESS_URL')]);
            URL::forceRootUrl(env('INGRESS_URL'));
        } else {
            // Default behavior
            $root = request()->root();
            Log::info('Using default', ['url' => $root]);
            URL::forceRootUrl($root);
        }
    }
}
