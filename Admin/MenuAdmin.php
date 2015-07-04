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
abstract class MenuAdmin extends \App\AdminBundle\Compiler\Cache\AdminCache {
    
    public function buildFormElement(\Symfony\Bundle\FrameworkBundle\Controller\Controller $controller, \Symfony\Component\Form\FormBuilder $builder, \App\AdminBundle\Compiler\Cache\AdminCache $admin, \App\AdminBundle\Compiler\Cache\ActionCache $action, $object, $property_name, $parent_property ) {
        $menu_group     = $object->menu_group ;
        if( in_array( $property_name, array('image') ) ) {
            if( !$menu_group->use_image  ) {
                return false ;
            } else {
                // change the image height, width ?
                
            }
        }
        parent::buildFormElement($controller, $builder, $admin, $action, $object, $property_name, $parent_property) ;
    }
    
    
    public function onUpdate(Controller $controller, Request $request, ActionCache $action, $menu , Form $form ){
        if( ! $menu->menu_group ) {
            throw new \Exception('menu no group');
        }
        
        $menu->menu_group->updated  = new \DateTime('now') ;
        $this->getManager()->persist( $menu->menu_group ) ;
        
        parent::onUpdate($controller, $request, $action, $menu, $form) ;
    }
    
    
    public function getListDQL(){
        $dql   = parent::getListDQL() ;
        $dql  .= ' ORDER BY a.order_by DESC';
        return $dql ;
    }
}