<?php

namespace App\AdminBundle\Compiler\MetaType\Form ;

use App\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType("apptree", map="*")
 */
class Tree extends Element {
    
    protected $copy_property ;
    
    protected function set_copy_property($value) {
        if( ! $this->admin_object->reflection->hasProperty( $value ) ) {
            $this->throwError("copy_property(%s) is not valid property of class `%s`", $value, $this->admin_object->class_name );
        }
        $this->copy_property    = $value ;
    }

        public function getFormOptions() {
        $options    = parent::getFormOptions() ;
        
        $options['admin_class']  = $this->admin_object->class_name ;
        $options['admin_property']  = $this->class_property ;
        $options['copy_property']  = $this->copy_property ;
        
        $map    = $this->admin_object->orm_metadata->getAssociationMapping( $this->class_property ) ;
        
        if ( \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY === $map['type']  ){
            
        } else {
            
        }
        
        $options['target_class']  = $map['targetEntity'] ;
        
        if( !$this->admin_object->generator->hasAdminClass( $options['target_class']) ) {
            throw new \Exception("oops");
        }
        
        return $options ;
    }
}
