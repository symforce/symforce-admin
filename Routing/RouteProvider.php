<?php

namespace Symforce\AdminBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\RouteProvider as  OrmProvider ;

class RouteProvider  implements \Symfony\Cmf\Component\Routing\RouteProviderInterface {
    
    /**
     * @var \Gedmo\Tree\Entity\Repository\NestedTreeRepository
     */
    protected $repos ;
    
    /**
     * @var string
     */
    protected $controller ;
    
    /**
     * @var string
     */
    protected $template ;
    
    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     */
    public function __construct(\Doctrine\Bundle\DoctrineBundle\Registry $doctrine, $entity_class , $controller, $template ) {
        $this->repos    = $doctrine->getManager()->getRepository( $entity_class ) ;
        $this->controller = $controller ;
        $this->template = $template ;
    }

    public function getRouteCollectionForRequest(Request $request){
        $collection = new RouteCollection();
        
        $path   = $request->getPathInfo() ;
        if( '/-1' === $path ) {
            return $collection ;
        }

        $slugs  = explode('/', trim($path, '/' ) );
        $slug   = array_pop( $slugs ) ;
        
        $page = $this->repos->findOneBy( array(
            'slug'  => $slug ,
        ));
        
        if( !$page ) {
            return $collection ;
        }

        $_page  = $page->getParent() ;
        while( count($slugs)){
            $_slug  = array_pop( $slugs ) ; 
            if( !$_page || $_slug !== $_page->getSlug() ) {
                return $collection ;
            }
            $_page  = $_page->getParent() ;
        }
        if( $_page ) {
            return $collection ;
        }
        
        $template   = $page->getPageTemplate() ;
        if( ! $template ) {
            $template   = $this->template  ;
        }
        
        $defaults   = array(
            '_controller'   => $this->controller ,
            '_page_template' => $template ,
            '_page_object'   => $page ,
        ) ;
        
        $requirements   = array() ;
        
        $route  = new Route( $path, $defaults, $requirements);
        
        $name   = 'app_page_' . $page->getSlug() ;
        
        $collection->add($name, $route);
        
        return $collection ;
    }
    
    public function getRouteByName($name){
        
    }
    
    public function getRoutesByNames($names){
        
    }
    
    public function findPageBySlug($slug)
    {
        // Find a page by slug property
        $page = $this->findOneBySlug($slug);

        if (!$page) {
            // Maybe any custom Exception
            throw $this->createNotFoundException('The page you are looking for does not exists.');
        }

        $pattern = $page->getUrl(); // e.g. "/first-level/second-level/third-level"

        $collection = new RouteCollection();

        // create a new Route and set our page as a default
        // (so that we can retrieve it from the request)
        $route = new Route($pattern, array(
            'page' => $page,
        ));

        // add the route to the RouteCollection using a unique ID as the key.
        $collection->add('page_'.uniqid(), $route);

        return $collection;
    }
}