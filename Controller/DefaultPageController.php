<?php

namespace App\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of DefaultPageController
 *
 * @author loong
 */
class DefaultPageController  extends Controller {
    
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \App\AdminBundle\Entity\Page
     */
    protected function getPage(Request $request) {
        $page   = $request->get('_page_object') ;
        return $page ;
    }


    final public function dispatchAction(Request $request){
        $page   = $this->getPage($request) ;
        
        return $this->render($request->get('_page_template'), array(
            'page'  => $page ,
        ) );
    }
}
