<?php

namespace Symforce\AdminBundle\Entity ;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Description of FileRepository
 *
 * @author loong
 */
class FileRepository extends EntityRepository implements ContainerAwareInterface {

    private $_url_pattern ;
    
    private $uuid_cache = array() ;

    /**
     * @var ContainerInterface
     *
     * @author Ashok Chitroda <ashok.chitroda@gmail.com>
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function loadByUUID( $_uuid ) {
        /**
         * @todo use apc cache in prod server
         */
        if( isset($this->uuid_cache[ $_uuid ]) ) {
            return $this->find( $this->uuid_cache[ $_uuid ] ) ;
        }
        
        $uuid   = array( pack('H*', str_replace('-', '', $_uuid)) ) ;
        $conn   = $this->_em->getConnection() ;
        $sql    = sprintf('SELECT id FROM %s  WHERE uuid = ? ', $this->_class->table['name'] ) ;
        
        $id     = $conn->fetchColumn($sql, $uuid, 0 ) ;
        if( $id ) {
            $file   = $this->find( $id ) ;
            if( $file ) {
                $this->uuid_cache[ $_uuid ] = $id ;
                return $file ;
            }
        }
        
        /*
        $uuid   =  pack('H*', str_replace('-', '', $_uuid)) ;
        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder( $this->_em );
        $rsm->addRootEntityFromClassMetadata( $this->_entityName, 'f') ;
        $query = $this->_em->createNativeQuery( sprintf('SELECT * FROM %s  WHERE uuid = ? ', $this->_class->table['name'] ) , $rsm);
        $query->setParameter(1, $uuid);
        $file = $query->getOneOrNullResult() ;
        return $file ; 
        */
    }

    private function getUrlPattern(){
        if( null == $this->_url_pattern ) {
            $this->_url_pattern = File::getAllFilesPattern( $this->container->getParameter('sf.web_assets_dir') ) ;
        }
        return $this->_url_pattern ;
    }
    
    public function loadByURL( $url ) {
        if( $url && preg_match( $this->getUrlPattern() , $url, $ma) ) {
            $file   = $this->loadByUUID( $ma[2] ) ;
            if( $file && $ma[3] === $file->getExt() ) {
                return $file ;
            }
        }
    }
    
    
    public function loadFilesForHtml( $class_name, $property_name, $entity_id, $session_id , $order_by = 'created', $asc = 'DESC' ) {
        $entity_id  = (int) $entity_id ;
        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder( $this->_em );
        $rsm->addRootEntityFromClassMetadata( $this->_entityName, 'f') ;
        if( $entity_id > 0 ) {
            $sql    = sprintf('SELECT * FROM %s WHERE class_name=? AND property_name=? AND entity_id=? AND (
                session_id=? OR session_id IS NULL
            ) ORDER BY %s %s ', $this->_class->table['name'] , $order_by , $asc ) ;
            $query  = $this->_em->createNativeQuery( $sql , $rsm ) ;
            $query->setParameter(1, $class_name);
            $query->setParameter(2, $property_name);
            $query->setParameter(3, $entity_id);
            $query->setParameter(4, $session_id);
        } else {
            $sql    = sprintf('SELECT * FROM %s WHERE class_name=? AND property_name=? AND entity_id=0 AND (
                session_id=? 
            ) ORDER BY %s %s ', $this->_class->table['name'] , $order_by , $asc ) ;
            $query  = $this->_em->createNativeQuery( $sql , $rsm ) ;
            $query->setParameter(1, $class_name);
            $query->setParameter(2, $property_name);
            $query->setParameter(3, $session_id);
        }
       
        return $query ;
    }
}
