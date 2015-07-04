<?php

namespace App\AdminBundle\Admin ;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\AdminBundle\Compiler\Cache\ActionCache ;
use Symfony\Component\Form\Form ;

/**
 * Description of PageAdmin
 *
 * @author loong
 */
abstract class MenuGroupAdmin extends \App\AdminBundle\Compiler\Cache\AdminCache {
    
    public function onUpdate(Controller $controller, Request $request, ActionCache $action, $menu , Form $form ){
        
        parent::onUpdate($controller, $request, $action, $menu, $form) ;
    }
    
}
