<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;
use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType(orm="text,string", default=true)
 */
class Textarea extends Text {
    
    public $required ;
    
    public $max_length  = 0x7fff ;
    public $hetigh = 12 ;
    
    public function set_height( $value ) {
        // todo check it
        $this->hetigh = $value ;
    }
    
    public function getFormOptions() {
        $_option    = $this->admin_object->orm_metadata->fieldMappings[ $this->class_property ] ;
        if( isset($_option['length']) ) {
            if( $this->max_length > $_option['length'] ) {
                $this->max_length = $_option['length'] ;
            }
        }
        $options    = parent::getFormOptions() ;
        
        $options['attr']['rows']  = $this->hetigh ;
        
        return $options ;
    }
}