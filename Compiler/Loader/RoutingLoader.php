<?php

namespace Symforce\AdminBundle\Compiler\Loader ;

class RoutingLoader extends \Symfony\Component\Config\Loader\Loader {
    
    /**
     * @var AnnotationLoader
     */
    public $container ;
    
    public function setContainer( $container ){
        $this->container    = $container ;
    }
    
    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null){
        return 'sf_route_resource' === $type ;
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     */
    public function load($resource, $type = null) {
        // load mete loader first to generate the admin cache  
        return $this->container->get('sf.route.loader')->getRouteCollection() ;
    }
}
