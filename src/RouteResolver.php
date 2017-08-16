<?php

/*
 * The MIT License
 *
 * Copyright 2017 Andrei Vassilenko <avassilenko2@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Crumby\RouteResolver;
use Crumby\RouteResolver\Contracts\ParamResolverCollection as ParamResolverCollection;
use Crumby\RouteResolver\Contracts\ParamResolver as ParamResolver;
/**
 * 
 * @author Andrei Vassilenko <avassilenko2@gmail.com>
 */
class RouteResolver {
    /*
     * key => object
     */
    protected $segmentsResolvers;
    public function __construct() {
        $this->loadConfig();
    }
    
    /**
     * Loads RouteResolver configuration. Named route parameter and Resolver to resolve it
     */
    public function loadConfig() {
        $this->segmentsResolvers = config('crumby-crumbs.route-resolver');
    }
    
    /**
     * Test request and dynamically creates classes to resolve properties of binded class
     * @return [] of Crumby\RouteResolver\ParamResolver
     */
    public function getFromRequest($route = null) {
        $resolvers = [];
        foreach ($this->segmentsResolvers as $key => $resolverClass) {
            if( $bindedClass = request()->route($key) ) {
                $resolvers[$key] = new $resolverClass($bindedClass);
            }
        }
        // sort resolvers in same order they are in request
        if (!empty($resolvers)) {
            if (empty($route)) {
                $route = \Route::getCurrentRoute()->uri();
            }       
            $segments = explode('/', $route);
            $sortedResolvers = [];
            foreach($segments as $segment) {
                $segment = trim($segment, "{}");
                if (isset($resolvers [$segment])) {
                    $sortedResolvers[$segment] = $resolvers[$segment];
                }
            }
            $resolvers = $sortedResolvers;
        }
        return empty($resolvers) ? false  : $resolvers;
    }
    
    /**
     * Returns array of resolved routes, if the route resolver has no collection() implementation returns false.
     * 
     * @param string $uriWithParam If route is null - use current route.
     * @param [] $resolvers If $resolvers are null - find resolvers by route.
     * @retun [] | false
     */
    public function resolveRouteCollection($uriWithParam = null, $resolvers = null) {
        if (empty($uriWithParam)) {
            $uriWithParam = \Route::getCurrentRoute()->uri();
        }
        if (empty($resolvers)) {
            $resolvers = \RouteResolver::getFromRequest($uriWithParam);
        }
        $allRoutes = [];
        
        foreach ($resolvers as $parameter => $resolver) {
            if ($resolver instanceof ParamResolverCollection) {
                if ($contentItems = $resolver->collection()) {
                    foreach ($contentItems as $item) {
                       $locale = $resolver->locale($item);
                      
                       $urlCurrent = $uriWithParam;
                       if (isset($allRoutes[$locale]['url'])) {
                           $urlCurrent = $allRoutes[$locale]['url'];
                       }
                       // build real url
                       // last values of label overwrites previous
                       $allRoutes[$locale] = [
                           'locale' => $locale,
                           'label' => $resolver->label($item),
                           'url' => str_replace('{'. $parameter . '}', $resolver->segment($item), $urlCurrent)
                           ];
                    }
                } 
            }
            else {
                throw new \Exception("Resolver has to implement of 'Crumby\RouteResolver\Contracts\ParamResolverCollection', " .  get_class($resolver) . "  given.");
            }
        }
        
        return empty($allRoutes) ? false : $allRoutes;
        
    }
    
    /**
     * Resolves route Locale, Label, Url
     * 
     * @param string $uriWithParam
     * @param [] $resolvers  Assosiative array, where key is dynamic parameter name, value is Crumby\RouteResolver\ParamResolver
     * @param boolean $trailingSlash If url starts from trailing slash
     * @return [] | false :
     *  [   'locale' => string,
            'label' => string,
            'url' => string
        ];
     * @throws \Exception
     */
    public function resolveRouteItem($uriWithParam = null, $resolvers = null, $trailingSlash = false) {
        if (empty($uriWithParam)) {
            $uriWithParam = \Route::getCurrentRoute()->uri();
        }
        if (empty($resolvers)) {
            $resolvers = \RouteResolver::getFromRequest($uriWithParam);
        }
        $resolvedRoute = [];
          
        if (!empty($resolvers)) {
            foreach ($resolvers as $parameter => $resolver) {
                if ($resolver instanceof ParamResolver) {
                    if ($item = $resolver->item()) {
                        $urlCurrent = $uriWithParam;
                        if (isset($resolvedRoute['url'])) {
                            $urlCurrent = $resolvedRoute['url'];
                        }
                        // build real url
                        // last values of label overwrites previous
                        $resolvedRoute = [
                            'locale' => $resolver->locale($item),
                            'label' => $resolver->label($item),
                            'url' => str_replace('{'. $parameter . '}', $resolver->segment($item), $urlCurrent)
                            ];
                    }
                }
                else {
                    throw new \Exception("Resolver has to implement of 'Crumby\RouteResolver\Contracts\ParamResolver', " .  get_class($resolver) . "  given.");
                }
            }
        }  

        /**
         * fix trailng slash
         */
        if ($trailingSlash && isset($resolvedRoute['url'])) {
            $resolvedRoute['url'] = '/' . ltrim($resolvedRoute['url'], '/');
        }
        return empty($resolvedRoute) ? false : $resolvedRoute;   
    }
    
}
