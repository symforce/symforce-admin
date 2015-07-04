<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType("sf_datetime", orm="string,datetime,date,time", default="detetime" )
 */
class Datetime extends Element {
    
    public $format = 'Y-m-d H:i' ;
    
    /** @var string */
    public $greater_than ;
    
    /** @var string */
    public $less_than ;
    
    public function set_format( $format ) {
        $this->format   = $format ;
    }
    
    public function getFormOptions() {
       $_options    = parent::getFormOptions() ; 
       
       /**
        * @TODO check $this->jsclass call js instance 
        */
       $options    = array(
           'format' => $this->format ,
           'picker' => 'datetime' ,
           'attr'  => array(
                'type'  => 'text' ,
                'class'   => 'datepicker not-removable form-control' ,
                'data-format' => $this->format ,
            )
        ) ;
        
       if ( null !== $this->greater_than ) {
           $options['greater_than'] = $this->greater_than ;
       }
       
       if ( null !== $this->less_than ) {
           $options['less_than'] = $this->less_than ;
       }
       
       return array_merge($_options, $options)  ;
    }
}