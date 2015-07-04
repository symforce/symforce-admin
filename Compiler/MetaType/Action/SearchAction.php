<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Action ;

class SearchAction  extends AbstractAction {
    
    public $property_annotation_class_name = 'Symforce\AdminBundle\Compiler\Annotation\Filter' ;
    public $template = 'AppAdminBundle:Admin:search.html.twig' ;
    
    public function addProperty( $property, \Symforce\AdminBundle\Compiler\Annotation\Annotation $annot ){
        $_property  = new SearchProperty($this->children , $this->admin_object, $property, $annot ) ;
    }
    
    /**
     * @return \Symforce\AdminBundle\Compiler\Generator\PhpClass
     */
    public function compile(){
         
    }

}
