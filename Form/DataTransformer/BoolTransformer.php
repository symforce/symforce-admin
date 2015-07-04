<?php

namespace App\AdminBundle\Form\DataTransformer;

class BoolTransformer implements  \Symfony\Component\Form\DataTransformerInterface {

    /**
     * Transforms an object (issue) to a string (number).
     */
    public function transform($value) {
        return $value ? 1 : 0 ;
    }

    /**
     * Transforms a string (number) to an object (issue).
     */
    public function reverseTransform($value){
        return boolval($value) ;
    }
}