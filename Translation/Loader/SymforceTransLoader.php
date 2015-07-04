<?php

namespace Symforce\AdminBundle\Translation\Loader;

use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\YamlFileLoader ;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Description of SymforceTransLoader
 *
 * @author loong
 */
class SymforceTransLoader extends YamlFileLoader
{
    public function setMetaLoader( $loader ) {
        
    }
}