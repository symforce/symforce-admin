<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType("sf_owner", map="Symforce\UserBundle\Entity\User")
 */
class Owner extends Element {
    
    public function getFormOptions(){
        $options    = parent::getFormOptions() ;
        $options['choices'] = $this->compilePhpCode( '$this->getOwnerFormChoices($object)' ) ;
        $options['sf_admin_class']  = $this->admin_object->class_name ;
        
        return $options ;
    }
}
