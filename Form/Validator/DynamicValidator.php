<?php

namespace Symforce\AdminBundle\Form\Validator;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Dynamic validator
 */
class DynamicValidator
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $reversed = array() ;
    
    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param string $key
     * @param string $invalidMessage
     * @param string|null $bypassCode
     */
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container        = $container ;
    }
    
    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data   = $event->getData() ;
        if( empty($data) ) {
            return ;
        }
        $form   = $event->getForm()->getParent() ;
        $object = $form->getData() ;
        $admin  = $this->container->get('sf.admin.loader')->getAdminByClass($object) ;
        $ps     = explode(',',  $data ) ;
        foreach($ps as $p) {
            $this->reversed[$p] = $admin->getReflectionProperty($p)->getValue($object) ;
        }
        $admin->addEvent('submit', function($_unknow) use( $admin, $form ){
            $object = $form->getData() ;
            foreach($this->reversed as $p => $value ) {
                 $admin->getReflectionProperty($p)->setValue($object, $this->reversed[$p] ) ;
            }
			
			/*
            foreach($this->reversed as $p => $value ) {
                if( $admin->isFieldVisiable($p, $object) ) {
                    throw new \Exception(sprintf("admin `%s` property `%s` shoud be show, but not hide ", $p, $admin->getName() ));
                    $error  = new FormError( __LINE__ );
                    $form->addError($error);
                }
            } 
			*/
            foreach($this->reversed as $p => $value ) {
                if( !$this->hasChild($form, $p) ) {
                    throw new \Exception(sprintf("admin `%s` property `%s` not exists! ", $p, $admin->getName() ));
                    $error  = new FormError( __LINE__ );
                    $form->addError($error);
                }
            }
        });
    }
    
    private function hasChild(\Symfony\Component\Form\Form $form, $name) {
        if( $form->has($name) ) {
            // check duplicate name  
            $child = $form->get( $name ) ;
            if( ! $child->isValid() ) {
                $this->clearError( $child ) ;
            }
            return true ;
        }
        foreach($form as $child) {
            if( $this->hasChild($child, $name) ) {
                return true ;
            }
        }
        return false ;
    }

    private function clearError(\Symfony\Component\Form\Form $form){
        $rc     = new \ReflectionObject( $form ) ;
        $prop   = $rc->getProperty('errors') ;
        $prop->setAccessible( true ) ;
        $prop->setValue($form, array() ) ;
        
        foreach($form as $child) {
            $this->clearError($child) ;
        }
    }
}
