<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

use Symforce\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType("textarea")
 */
class Json extends Textarea {
    
    public $hetigh = 3 ;
    
    
    public function getFormOptions() {
        $options    = parent::getFormOptions() ;
        
            $writer = new \Symforce\AdminBundle\Compiler\Generator\PhpWriter();
            $writer
               ->writeln('new \Symforce\AdminBundle\Form\Constraints\Json(array(') 
               ->indent()
                   // ->writeln( sprintf(' "min" => %d, ',  $min )) 
               ->outdent() 
               ->writeln('))')
               ;
            $options['constraints'][]   = $this->compilePhpCode( $writer->getContent() ) ;
            
        return $options ;
    }
}