<?php

namespace Symforce\AdminBundle\Compiler\Cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Description of ViewAction
 *
 * @author loong
 */
class ViewActionCache extends ActionCache  {
    
    public function isRequestObject() {
        return true ;
    }
    
    public function isViewAction() {
        return true ;
    }
    
    public function isPropertyAction() {
        return true ;
    }
    
    public function isWorkflowAction() {
        return true ;
    }
    
    public function onController(Controller $controller, Request $request){
       
        $object = $this->admin->getRouteObject() ;
        
        $label  = null ;
        if( $this->admin->tree && $this->admin->getTreeObjectId() ) {
            $label = $this->admin->trans('sf.tree.create.title', array(
                        '%object%' => $this->admin->string(  $this->admin->getTreeObject() ) ,
                        '%admin%' => $this->admin->getLabel() ,
                       ) , $this->sf_domain 
                   ) ;
        } else {
            $label = $this->admin->getFormLabel() ;
        }
        
        return $controller->render( $this->template , array(
            'sf_admin_loader' =>  $controller->get('sf.admin.loader') , 
            'admin' => $this->admin ,
            'action' => $this ,
            'object' => $object ,
            'title' => $label ,
        ) );
    }
}
