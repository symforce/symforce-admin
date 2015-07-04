<?php

namespace Symforce\AdminBundle\Form\Constraints ;


/**
 * @author loong
 */
class Json extends \Symfony\Component\Validator\Constraint {
    public $message = 'This value is not a valid json.' ;
}
