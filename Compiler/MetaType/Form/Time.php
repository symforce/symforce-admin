<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType(default="time")
 */
class  Time extends DateTime {
    
    public $format = 'H:i:s' ;
    
    public function getFormOptions() {
       $options    = parent::getFormOptions() ; 
       $options['picker']   = 'time' ;
       return $options ;
    }
} 