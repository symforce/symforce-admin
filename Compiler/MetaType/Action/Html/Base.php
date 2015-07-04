<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Action\Html ;

/**
 * Description of AbstractTag
 *
 * @author loong
 */
abstract class Base extends \Symforce\AdminBundle\Compiler\MetaType\Type {
    
    public $code ;
    
    public function set_code( $code ) {
        $this->code = $code ;
    }
}
