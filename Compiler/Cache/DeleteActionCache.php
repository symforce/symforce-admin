<?php

namespace Symforce\AdminBundle\Compiler\Cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of DeleteAction
 *
 * @author loong
 */
class DeleteActionCache extends ActionCache {
    
    public function isRequestObject() {
        return true ;
    }
    
    public function isDeleteAction() {
        return true ;
    }
    
    public function isOwnerAction() {
        return true ;
    }
    
    public function isPropertyAction() {
        return false ;
    }
    
    public function onController(Controller $controller, Request $request){
        
        $object = $this->admin->getRouteObject() ;
        $list_url   = $this->admin->path('list') ;
        if( ! $object ) {
            $request->getSession()->getFlashBag()->add('error', 'not exists!' ) ;
            return $controller->redirect( $this->admin->path('list') ) ;
        }
        
        $admin_children = array() ;
        $children = $this->admin->getAdminRouteChildren() ;
        if($children) foreach($children as $child_admin_name => $o ) {
            if( $o[0] ) {
                throw new \Exception("unimplement") ;
            }
            $admin_children[ $child_admin_name] = array() ;
            $child_admin    = $this->admin->getAdminLoader()->getAdminByName( $child_admin_name ) ;
            $properties     = $o[0] ? $o[1] : array( $o[1] ) ;
            foreach($properties  as $config ) {
                $child_property = $config[0] ;
                $my_property = $config[1] ;
                $count  = $child_admin->countBy( $child_property, $object );
                $admin_children[ $child_admin_name][ $child_property ] = $count ;
            }
        }
        
        /**
         * @var \Symfony\Component\Form\FormBuilder
         */
        $builder    = $controller->createFormBuilder( $object , array(
            'label' => $this->admin->getFormLabel() ,

            'constraints'   => array(
                new \Symfony\Component\Validator\Constraints\Callback(function($object, \Symfony\Component\Validator\Context\ExecutionContext $context ) use($controller, $admin_children ){

                    foreach($admin_children as $child_admin_name => $list ) {
                        $child_admin    = $this->admin->getAdminLoader()->getAdminByName( $child_admin_name ) ;
                        foreach($list  as $count ) {
                            if( $count > 0 ) {
                                if( !$child_admin->auth('delete') ) {
                                    $error   = $this->admin->trans('sf.action.delete.error.child', array(
                                        '%admin%'    => $this->admin->getLabel() ,
                                        '%child%'    => $child_admin->getLabel() ,
                                        '%count%'    => $count ,
                                    ), $this->sf_domain );
                                    $context->addViolation( $error) ;
                                } 
                            }
                        }
                    }
                    
                }) ,
            ) ,
        )) ;
        
        $this->buildFormReferer($request, $builder, $object, $list_url);
        $form     = $builder->getForm() ;
        $this->setForm($form);
        
        $dispatcher = $this->admin->getService('event_dispatcher');
        $event = new \Symforce\AdminBundle\Event\FormEvent($form, $request);
        $dispatcher->dispatch('sf.event.form', $event) ;
        if (null !== $event->getResponse()) {
            return $event->getResponse() ;
        }
        
        if( $request->isMethod('POST') ) {
             $form->bind($request);
             $this->admin->fireEvent( 'submit', $form ) ;
             if ($form->isValid())  {
                
                $msg = $this->trans( 'sf.action.delete.finish' , $object ) ;
                $this->admin->remove( $object ) ;
                $request->getSession()->getFlashBag()->add('info', $msg ) ;

                return $controller->redirect( $this->getFormReferer($form) ) ;
             }
        }
        
        return $controller->render( $this->template , array(
            'sf_admin_loader' =>  $controller->get('sf.admin.loader') , 
            'admin' => $this->admin ,
            'action' => $this ,
            'form'  => $form->createView() ,
        ) );
    }
    
}
