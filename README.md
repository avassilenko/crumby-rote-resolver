Installation:
> composer require crumby/route-resolver
> php artisan vendor:publish --tag=config

1. Register service and facade. 
File: config/app.php

'providers' => [
    ......................
    'Crumby\RouteResolver\RouteResolverServiceProvider',
    ........................
 ];
 
 'aliases' => [ 
    ......................
    'RouteResolver' => 'Crumby\RouteResolver\Facades\RouteResolver',
    ......................
 ];
      
2. Add route parameter and corresponding resolver class to config/route-resolver.php 
return [
        .........................
        'package' => 'App\Resolvers\PackageResolver',
        ........................
];
when route resolve route /packages/{package} it calls Resolver Service and substitute last segment of url {package} with localized value returned by Resolver Class method segment(), in our example PackageResolver::segment(). 

3. Implement interface Crumby\RouteResolver\Contracts\ParamResolver.php
in file, for example:
app/Resolvers/PackageResolver.php

4. make sure the App\Resolvers\PackageResolver class exists in autoloader
    