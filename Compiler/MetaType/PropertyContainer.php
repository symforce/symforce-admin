<?php

namespace Symforce\AdminBundle\Compiler\MetaType ;

/**
 * @author loong
 */
class PropertyContainer {
    
    /**
     * @var array 
     */
    public $properties = array() ;
    
    /**
     * @var EntityAware 
     */
    public $parent ; 
    
    /**
     * @var Admin\Entity 
     */
    public $entity ; 
    
    /**
     * @var \Symforce\AdminBundle\Compiler\Generator\TransGeneratorNode 
     */
    public $tr_node ;
    
    /**
     * @var string
     */
    public $tr_domain ;
    
    /**
     * @var string 
     */
    public $sf_domain ;
    
    public function __construct(Admin\EntityAware $parent, Admin\Entity $entity = null ) {
        $this->parent = $parent ;
        if( null === $entity ) {
            $this->entity = $parent->admin_object ;
        } else {
            $this->entity = $entity ;
        }
    }
    
    public function addProperty(PropertyAbstract $property) {
        $this->properties[ $property->class_property ] = $property ;
    }
    
    public function hasProperty( $name ) {
        return isset( $this->properties[$name]) ;
    }
    
    public function getProperty( $name ) {
        return $this->properties[$name] ;
    }

    public function __get($name)
    {
        throw new Exception( \sprintf("%s is not valid property", $name) );
    }

    public function __set($name, $value)
    {
        throw new Exception( \sprintf("%s is not valid property", $name) );
    }
}