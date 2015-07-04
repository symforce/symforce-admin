<?php

namespace Symforce\AdminBundle\Form\DataTransformer ;

use Symfony\Component\Form\DataTransformerInterface ;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Symforce\AdminBundle\Entity\File ;
use Symforce\AdminBundle\Entity\TmpFile ;
use Doctrine\ORM\Id\UuidGenerator ;

/**
 * Description of DatetimeTransformer
 *
 * @author loong
 */
class EmbedHiddenTransformer implements DataTransformerInterface {

    /**
     * @var \Symforce\AdminBundle\Compiler\Loader\AdminLoader
     */
    private $admin_loader ;
    private $entity_class ;
    private $reverse_data ;


    public function __construct(\Symforce\AdminBundle\Compiler\Loader\AdminLoader $admin_loader, $entity_class ) {
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
            return 0 ;
        }
        if( !is_object($value) ) {
            return $value ;
        }
        $admin  = $this->admin_loader->getAdminByClass($this->entity_class) ;
        return $admin->getId($value) ;
    }
    
    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $value
     * @return \File|null
     */
    public function reverseTransform($value) {
        if( ! $this->reverse_data ) {
            throw new \Exception;
        }
        return $this->reverse_data ;
    }
    
    public function setReverseData($data) {
        if( !($data instanceof $this->entity_class) ) {
            throw new \Exception;
        }
        $this->reverse_data = $data ;
    }
    
}
