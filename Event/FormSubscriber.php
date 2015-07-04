<?php

namespace Symforce\AdminBundle\Event;
 
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class FormSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    
    private $is_xhr_request ;

    public static function getSubscribedEvents()
    {
        return array(
            'app.event.form'     => array('onFormEvent', 0),
        );
    }
    
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container ;
    }
    
    public function isXhrRequest() {
        return $this->is_xhr_request ;
    }
    
    private function getErrors(\Symfony\Component\Form\Form $form, array & $elements, array & $forms, array & $errors ){
        $name = array_shift($elements) ;
        if( $form->has($name) ) {
            $child    = $form->get($name) ;
            $forms[ $name ] = $child ;
            $errors[ $name ] = $child->getErrors() ;
            if( empty($elements) ) {
                return $name ;
            }
            return $this->getErrors($child, $elements, $forms, $errors ) ;
        } else {
            if( count($forms) ) {
                $last_name  = null ;
                foreach($forms as $name => $child ) {
                    $last_name  = $name ;
                }
                return $last_name ;
            }
            return null ;
        }
    }

    public function onFormEvent(FormEvent $event)
    {
        if( !isset($_POST['app_validate_element']) ) {
            return ;
        }
        $form_element_name  = $_POST['app_validate_element'] ;
        if( !preg_match_all('/\[(.+?)\]/', $form_element_name , $matches ) ) {
            return ;
        }
        
        $this->is_xhr_request   = true ;
        
        $form       = $event->getForm() ;
        $elements   = $matches[1] ;
        
        $request    = $event->getRequest() ;
        $form->bind($request);
        
        $errors     = array() ;
        $forms      = array() ;
        
        $element    = $this->getErrors($form,  $elements, $forms, $errors );
        
        if( ! $element ) {
            $json   = array(
                'errno' => null ,
                'element' => $elements[0] ,
                'error' => array() ,
            );
        } else {
            $json   = array(
                'errno' => __LINE__ ,
                'element' => $element ,
                'name'   => $form->getName() . '[' . join('][', array_keys($forms) ) . ']', 
                'error' => array() ,
                'valid' => true ,
            );
            $_errors    = $errors[ $element ] ;
            if( !empty( $_errors ) ) {
                $json['valid']  = false ;
                foreach($_errors as $_error) {
                    $json['error'][]    = $_error->getMessage() ;
                }
            }
        }
        
        $response   = new \Symfony\Component\HttpFoundation\JsonResponse($json) ;
        $event->setResponse($response) ;
    }
    
}