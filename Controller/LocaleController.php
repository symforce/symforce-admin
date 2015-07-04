<?php

namespace Symforce\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LocaleController extends Controller
{

   /**
     * @Route("/locale/{inline}", name="sf_admin_locale", requirements={"inline"="(0|1)"})
     * @Template()
     */
    public function localeAction(Request $request, $inline = 0 )
    {
        // AppAdminBundle:Locale:locale.html.twig
        
        $service    = $this->container->get('sf.locale.listener');
        $form   = $service->getForm($request, $inline ) ;
        
        if( 'POST' === $request->getMethod() && !$inline ) {
        	$form->bind( $request ) ; 
        	if ($form->isValid()) { 
                    $locale = $form->getData() ;
                    $request->getSession()->set( 'sf_locale' ,  $locale->getLocale() ) ;
                    
                    return $this->redirect(  $locale->getRedirectUrl() ) ;
        	} 
        }
        
        return array(
            'form' => $form->createView(),
        ) ;
    } 
}