<?php

namespace Symforce\AdminBundle\Form\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author loong
 */
class JsonValidator extends ConstraintValidator {
    
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ( empty($value) ) {
            return ;
        }
        $obj    = @json_decode( $value, true ) ;
        if ( ! $obj ) {
            $this->context->addViolation($constraint->message);
        }
    }
}
