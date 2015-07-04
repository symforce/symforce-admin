<?php

namespace App\AdminBundle\Form\DataTransformer ;

use Symfony\Component\Form\DataTransformerInterface ;
use Symfony\Component\Form\Exception\TransformationFailedException;

use App\AdminBundle\Entity\File ;
use App\AdminBundle\Entity\TmpFile ;
use Doctrine\ORM\Id\UuidGenerator ;

/**
 * Description of DatetimeTransformer
 *
 * @author loong
 */
class TreeTransformer implements DataTransformerInterface {

    /**
     * @var \App\AdminBundle\Compiler\Loader\AdminLoader
     */
    private $admin_loader ;
    private $entity_class ;

    public function __construct(\App\AdminBundle\Compiler\Loader\AdminLoader $admin_loader, $entity_class ) {
        $this->admin_loader = $admin_loader ;
        $this->entity_class = $entity_class ;
    }
    
    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  object|null $value
     * @return string
     */
    public function transform($value) {
        if( !$value ) {
            return array(
                'id'    => 0 ,
                'string'   => '' ,
            ) ;
        }
        $admin  = $this->admin_loader->getAdminByClass( $this->entity_class) ;
        return array(
            'id'    => $admin->getId( $value ) ,
            'string'    => $admin->string( $value ) ,
        ) ;
    }
    
    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $value
     * @return \File|null
     */
    public function reverseTransform($value) {
        if( !$value ) {
            return null ;
        }
        return $this->admin_loader->getAdminByClass( $this->entity_class)->getObjectById( $value ) ;
    }
}
