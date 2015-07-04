<?php


namespace Symforce\AdminBundle\Compiler\MetaType\Admin ;

use Symforce\AdminBundle\Compiler\Annotation\Annotation ;

class ActionCollection {
    
    /**
     * @var array 
     */
    public $children = array() ;

    public function __construct(Entity  $entity) {
        
        $annotations = array() ;
        if( isset($entity->cache->class_annotations['Symforce\AdminBundle\Compiler\Annotation\Action']) ) {
            $annotations = $entity->cache->class_annotations['Symforce\AdminBundle\Compiler\Annotation\Action'] ;
        }
        
        $default_actions    =  array( 
                'view'  => '\Symforce\AdminBundle\Compiler\MetaType\Action\ViewAction' ,
            
                'create' => '\Symforce\AdminBundle\Compiler\MetaType\Action\CreateAction' ,
                'update' => '\Symforce\AdminBundle\Compiler\MetaType\Action\UpdateAction' ,
                'delete' => '\Symforce\AdminBundle\Compiler\MetaType\Action\DeleteAction' ,
            
                'list' => '\Symforce\AdminBundle\Compiler\MetaType\Action\ListAction' ,
            
                'search' => '\Symforce\AdminBundle\Compiler\MetaType\Action\SearchAction' ,
                'batch' => '\Symforce\AdminBundle\Compiler\MetaType\Action\BatchAction' ,
            
                'page' => '\Symforce\AdminBundle\Compiler\MetaType\Action\PageAction' ,
            ) ;
        
        $autoload_actions   = array( 'list', 'create', 'update', 'delete' , 'view', 'page' );
        
        foreach($default_actions as $action_name => $action_class_name ) {
            
            if( 'page' === $action_name && !$entity->page ) {
                continue ;
            }
            
            if( !in_array($action_name, $autoload_actions) && !isset($annotations[$action_name])) {
                continue ;
            }
            
            if( isset($annotations[$action_name]) ) {
                 $action         = new $action_class_name( $action_name, $entity, $annotations[$action_name]) ;
                 unset($annotations[$action_name]) ;
            } else {
                $action         = new $action_class_name( $action_name, $entity) ;
            }
            $this->children[$action_name] = $action ;
        }
        
        // add CustomizeAction
        foreach($annotations as $action_name => $annot ) {
            $action         = new \Symforce\AdminBundle\Compiler\MetaType\Action\CustomizeAction( $action_name, $entity, $annot ) ; 
            $this->children[$action_name] = $action ;
        }
    }
    
    private $lazy_initialized ;
    public function lazyInitialize() {
        if( $this->lazy_initialized  ) {
            throw new \Exception('big error') ;
        }
        $this->lazy_initialized = true ;
        
        foreach($this->children as $action) {
            $action->lazyInitialize() ;
        }
        
    }
    
    public function addClassAction(\Symforce\AdminBundle\Compiler\Annotation\Action $annot ){
        try{
            $name   = $annot->name ;
            
            if( $this->children->hasKey($name) ) {
                $action = $this->children->get($name) ;
                if( !is_object($action) ) {
                    $this->throwError("`%s` is is not action(%s) object", json_encode($action), $name ) ;
                } else if(  $action->isCustomize() ) {
                    $this->throwError("`%s(%s)` is duplicate",  $action->getMeteTypeName(), $name ) ;
                } else {
                    $action->setMyPropertie( $annot ) ;
                }
            } else {
                
                $action_class   = $this->getActionClassName( 'customize' ) ;
                $action         = new  $action_class( $this->getAdminObject() , $annot ) ;
                $action->setName( $name ) ;
                
                $this->children->put($name, $action) ;
            }
        }catch( \Symforce\AdminBundle\Compiler\MetaType\Exception $e ){
            $this->throwError( $e->getMessage() );
        }
    }
}
