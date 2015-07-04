<?php

namespace Symforce\AdminBundle\Compiler\Loader;

use Symfony\Component\Validator\Mapping\Loader\LoaderInterface ;
use Symfony\Component\Validator\Mapping\ClassMetadata ;

/**
 * Description of ValidatorLoader
 *
 * @author loong
 */
class ValidatorLoader implements LoaderInterface {
    //put your code here
    
    /**
     * @var \Symforce\AdminBundle\Compiler\Cache\AdminLoader
     */
    protected $loader ;

    public function setAdminLoader(AdminLoader $loader ) {
        $this->loader   = $loader ;
    } 
    
    public function loadClassMetadata(ClassMetadata $metadata) {
        
        $class_name = $metadata->getReflectionClass()->getName()  ;
        
        if( $this->loader->hasAdminClass($class_name) ) {
            $admin  = $this->loader->getAdminByClass($class_name) ;
            $admin->loadValidatorMetadata( $metadata ) ;
            return true ;
        }
    }
}
