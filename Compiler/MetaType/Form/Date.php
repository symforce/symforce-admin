<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType(default="date")
 */
class  Date extends DateTime {
    public $format = 'Y-m-d' ;
    public function getFormOptions() {
       $options    = parent::getFormOptions() ; 
       $options['picker']   = 'date' ;
       return $options ;
    }
} 