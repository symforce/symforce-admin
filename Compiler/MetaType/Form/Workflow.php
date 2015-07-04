<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType("sf_workflow", orm="integer,string")
 */
class Workflow extends Element {
    
    public function getFormOptions(){
        $options    = parent::getFormOptions() ;

        $options['choices'] = $this->compilePhpCode( '$this->getWorkflowFormChoices($object)' ) ;
        
        $options['sf_admin_class']   = $this->admin_object->class_name ;
        $options['expanded']    = true ;
        $options['widget_type'] = 'inline' ;
        
        return $options ;
    }
}