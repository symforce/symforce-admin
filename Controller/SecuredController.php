<?php

namespace Symforce\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Validator\Constraints as Asset ;


class SecuredController extends Controller
{
    /**
     * @Route("/login", name="sf_admin_login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        
        $form   = $this->crateForm($request) ;
        
        $dispatcher = $this->container->get('event_dispatcher');
        $event = new \Symforce\AdminBundle\Event\FormEvent($form, $request);
        $dispatcher->dispatch('sf.event.form', $event) ;
        if (null !== $event->getResponse()) {
            return $event->getResponse() ;
        } 
        
        return array(
            'form'  => $form->createView() ,
        );
    }

    /**
     * @Route("/login_check", name="sf_admin_check")
     */
    public function securityCheckAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/logout", name="sf_admin_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
    
    
    
    protected function crateForm(\Symfony\Component\HttpFoundation\Request $request) {
        
        if ( $request->attributes->has(SecurityContext::AUTHENTICATION_ERROR) ) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
            $request->getSession()->set( SecurityContext::AUTHENTICATION_ERROR , null ) ;
        }
        
        $tr = $this->container->get('translator') ;
        $sf_domain  = $this->container->getParameter('sf.admin.domain') ;
      
        $builder = $this->container->get('form.factory')->createNamedBuilder('login', 'form', array(
            'label'  => 'sf.login.label' ,
            'translation_domain' => $sf_domain ,
        )) ; 
        
        $builder
                    ->add('username', 'text', array(
                        'label' => 'sf.login.username.label' ,
                        'translation_domain' => $sf_domain ,
                        'data'  => $request->getSession()->get(SecurityContext::LAST_USERNAME) ,
                        'horizontal_input_wrapper_class' => 'col-xs-6',
                        'attr' => array(
                            'placeholder' => 'sf.login.username.placeholder' ,
                        )
                    ) )
                    ->add('password', 'password', array(
                        'label'  => 'sf.login.password.label' ,
                        'translation_domain' => $sf_domain ,
                        'horizontal_input_wrapper_class' => 'col-xs-6',
                        'attr' => array(
                            
                        )
                    ) )
                
                    ->add('captcha', 'sf_captcha', array(
                        'label' => 'sf.form.captcha.label' ,
                        'translation_domain' => $sf_domain ,
                    ))
                
                ;
        $form     = $builder->getForm() ;
        
        if( $error ) {
            if( $error instanceof \Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException ) {
                $_error = new \Symfony\Component\Form\FormError( $tr->trans('sf.login.error.crsf', array(), $sf_domain ) ) ;
                $form->addError( $_error  ) ;
            } else if ( $error instanceof \Symforce\UserBundle\Exception\CaptchaException ) {
                $_error = $tr->trans('sf.login.error.captcha' , array(), $sf_domain ) ;
                if( $this->container->getParameter('kernel.debug') ) {
                    $_error .= sprintf(" code(%s)",  $error->getCode()  ) ;
                }
                $_error = new \Symfony\Component\Form\FormError( $_error );
                $form->get('captcha')->addError( $_error ) ;
            } else if( $error instanceof \Symfony\Component\Security\Core\Exception\BadCredentialsException ) { 
                $_error = new \Symfony\Component\Form\FormError( $tr->trans('sf.login.error.credentials' , array(), $sf_domain ) ) ;
                $form->get('username')->addError( $_error ) ;
            }  else if( $error instanceof \Symfony\Component\Security\Core\Exception\DisabledException ) {
                $_error = new \Symfony\Component\Form\FormError( $tr->trans('sf.login.error.disabled' , array(), $sf_domain ) ) ;
                $form->get('username')->addError( $_error ) ;
            } else {
                $_error = new \Symfony\Component\Form\FormError( $error->getMessage() ) ;
                if( $this->container->getParameter('kernel.debug') ) {
                    \Dev::dump(  $error ) ;
                }
                $form->get('username')->addError( $_error ) ;
            }
        }
        
        return $form ;
    }
}
