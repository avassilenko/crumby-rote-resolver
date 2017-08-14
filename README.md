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
      
2. Add route parameter and resolver class to config/route-resolver.php 
return [
        .........................
        'package' => 'App\Resolvers\PackageResolver',
        ........................
];
when route will resolve /packages/{package} we will call Resolver Service and insert last segment of url base on current locale  

3. Implement interface Crumby\RouteResolver\Contracts\ParamResolver.php
in file, for example:
app/Resolvers/PackageResolver.php

4. make sure the App\Resolvers\PackageResolver class exists in autoloader
    