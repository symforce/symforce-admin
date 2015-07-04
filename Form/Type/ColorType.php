<?php

namespace Symforce\AdminBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType ;

// http://github.com/xaguilars/bootstrap-colorpicker

class ColorType extends TextType {
    
    public function getName(){
        return 'sf_color' ;
    }
    
    public function getExtendedType()
    {
        return 'text';
    }
}
