<?php

namespace Symforce\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller; 

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminController extends Controller 
{
    
    /**
     * @var \Symforce\AdminBundle\Compiler\Cache\Loader
     */
    private $loader ;
    
    public function adminAction(Request $request)
    { 
        $this->loader   = $this->container->get('sf.admin.loader') ;

        $cache  = $this->container->get('sf.page.service') ;
        $option = $cache->getAdminOption( $request->attributes->get('_sf_route_name') ) ;
        $action = $option['dispatcher']($this->loader, $request) ;
        
        if( !$this->loader->auth( $action->getAdmin()->getName(), $action->getName()) ) {
            throw new AccessDeniedException();
        }

        return $action->onController( $this, $request );
    }
    
}
