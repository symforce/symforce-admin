<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType("sf_color", orm="string")
 */
class Color extends Element {
    
    public function getFormOptions(){
        $_options    = parent::getFormOptions() ; 
        
        $options    = array(
            'attr'  => array(
                'type'  => 'text' ,
                'class'   => 'colorpicker form-control not-removable' ,
            )
        ) ;
        
        return array_merge($_options, $options)  ;
    }
}