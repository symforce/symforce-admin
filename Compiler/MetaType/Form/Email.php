<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType(guess="mail")
 */
class Email extends Text {
    
    public function getFormOptions(){
        $options    =  parent::getFormOptions() ; 
        
        $writer = new \Symforce\AdminBundle\Compiler\Generator\PhpWriter();
        $writer
           ->writeln('new \Symfony\Component\Validator\Constraints\Email(array(')  
           ->indent()
               ->writeln( '"checkMX" => false , ') 
           ->outdent() 
           ->writeln('))')
           ;
        $options['constraints'][]   = $this->compilePhpCode( $writer->getContent() ) ;

        return $options ;
    }
    
}