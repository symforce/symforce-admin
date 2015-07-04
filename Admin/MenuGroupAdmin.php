<?php

namespace Symforce\AdminBundle\Admin ;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symforce\AdminBundle\Compiler\Cache\ActionCache ;
use Symfony\Component\Form\Form ;

/**
 * Description of PageAdmin
 *
 * @author loong
 */
abstract class MenuGroupAdmin extends \Symforce\AdminBundle\Compiler\Cache\AdminCache {
    
    public function onUpdate(Controller $controller, Request $request, ActionCache $action, $menu , Form $form ){
        
        parent::onUpdate($controller, $request, $action, $menu, $form) ;
    }
    
}
