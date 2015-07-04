<?php

namespace App\AdminBundle\Compiler\MetaType\Form ;

use App\AdminBundle\Compiler\Annotation\FormType ;

/**
 * @FormType("textarea")
 */
class Json extends Textarea {
    
    public $hetigh = 3 ;
    
    
    public function getFormOptions() {
        $options    = parent::getFormOptions() ;
        
            $writer = new \App\AdminBundle\Compiler\Generator\PhpWriter();
            $writer
               ->writeln('new \App\AdminBundle\Form\Constraints\Json(array(') 
               ->indent()
                   // ->writeln( sprintf(' "min" => %d, ',  $min )) 
               ->outdent() 
               ->writeln('))')
               ;
            $options['constraints'][]   = $this->compilePhpCode( $writer->getContent() ) ;
            
        return $options ;
    }
}