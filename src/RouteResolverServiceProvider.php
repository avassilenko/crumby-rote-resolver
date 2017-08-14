<?php
namespace Crumby\RouteResolver;

use Illuminate\Support\ServiceProvider;

class RouteResolverServiceProvider extends ServiceProvider
{
    const ROUTE_RESOLVER_VAR_NAME = 'RouteResolver';
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(self::ROUTE_RESOLVER_VAR_NAME, function ($app) {
            $resolver = new RouteResolver();

            \View::share(self::ROUTE_RESOLVER_VAR_NAME, $resolver);
            return $resolver;
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/crumby-crumbs/route-resolver.php' => config_path('crumby-crumbs/route-resolver.php')
            ], 'config');
        }
        
        $this->app->alias(self::ROUTE_RESOLVER_VAR_NAME, 'Crumby\RouteResolver\RouteResolver');
        \RouteResolver::loadConfig();
    }
}
