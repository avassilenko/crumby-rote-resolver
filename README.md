Installation:
-------------
```
> composer require crumby/route-resolver
> php artisan vendor:publish --tag=config
```

Register service and facade:
----------------------------
File: config/app.php

```
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
```
      
Add file PackageResolver.php to your project
--------------------------------------------
```
    <?php
    namespace App\Resolvers;

    use Crumby\RouteResolver\Contracts\ParamResolver as ParamResolver;
    use Crumby\RouteResolver\Contracts\ParamResolverCollection as ParamResolverCollection;
    use App\Content as Content;

    /**
     * Description of PackageResolver
     *
     * @author Andrei Vassilenko <avassilenko2@gmail.com>
     */
    class PackageResolver implements ParamResolver, ParamResolverCollection {
        protected $service;
        public function __construct(Content $content) {
            $this->service = $content;
        }
        public function collection($param=null) {
            return Content::fillIds($this->service->getIdsWithSameAttribute('content_group'), ['att' => 'content_locale', 'excl' => 'value']);
        }
        public function item($param=null) {
            return Content::fillIds([$this->service->id], ['att' => 'content_locale', 'excl' => 'value'])->first();
        }
        public function label($item=null) {
           return $item->title;
        }
        public function locale($item=null) {
            return $item->attr_value;    
        }
        public function segment($item=null) {
            return $item->slug;
        }
    }
```

Add route parameter and corresponding resolver class to config/route-resolver.php 
---------------------------------------------------------------------------------
```
return [
        .........................
        'package' => 'App\Resolvers\PackageResolver',
        ........................
];
```
when route resolve route /packages/{package} it calls Resolver Service and substitute last segment of url {package} with localized value returned by Resolver Class method segment(), in our example PackageResolver::segment(). 

Implement interface Crumby\RouteResolver\Contracts\ParamResolver.php
--------------------------------------------------------------------
in file, for example:
app/Resolvers/PackageResolver.php

Make sure the App\Resolvers\PackageResolver class exists in autoloader
----------------------------------------------------------------------
    
Example of building all existing localized urls for language switcher: 
----------------------------------------------------------------------
```
$resolvers = \RouteResolver::getFromRequest()
// below returns something like /packages/{package}  
$uriWithParam = \Route::getCurrentRoute()->uri();
$allRoutes = \RouteResolver::resolveRouteCollection($uriWithParam, $resolvers);
```

