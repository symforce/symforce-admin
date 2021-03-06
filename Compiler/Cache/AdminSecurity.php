<?php

namespace Symforce\AdminBundle\Compiler\Cache ;

trait  AdminSecurity {
    
    /**
     * @var array
     */
    protected $_auth_parents ;
    
    /**
     * @return \Symforce\UserBundle\Entity\User
     */
    public function getCurrentLoginUser() {
        return $this->admin_loader->getCurrentLoginUser() ;
    }
    
    public function auth( $action, $object = null ){
        $securityContext = $this->container->get('security.context') ;
        $user   = $securityContext->getToken()->getUser() ;
        $group  = $user instanceof \Symforce\UserBundle\Entity\User ? $user->getUserGroup() : null ;
        
        $is_visiable    = false ;
        if ( $securityContext->isGranted('ROLE_SUPER_ADMIN') ) {
             $is_visiable = true ;
        } else if( $group ) {
            $is_visiable = $group->auth( $this->name , $action) ;
        }
        
        if( !$action ) {
            if( $object ) {
                throw new \Exception('object must with action');
            }
            return $is_visiable ;
        }
        
        if( $is_visiable && $object && $this->property_owner_name ) {
            if( $group ) {
                if( $group->isOwnerVisiable( $this->name, $action ) ) {
                    // check owner match
                    $owner_user = $this->getReflectionProperty( $this->property_owner_name )->getValue($object) ;
                    $is_visiable    = $user->isEqual( $owner_user ) ;
                }
            }
        }

        if( $is_visiable && $object && $this->tree && isset($this->tree['leaf']) ) {
            $is_leaf    = $this->getReflectionProperty( $this->tree['leaf'] )->getValue( $object ) ;
            if( $is_leaf ) {
                $action     = $this->getAction( $action ) ;
                if( $action->isListAction() ) {
                    return false ;
                }
            }
        }

        $_action    = $this->getAction($action) ; 
        if( $this->workflow && $is_visiable ) {
            if( $_action->isWorkflowAction() || $_action->isDeleteAction() ) {
                if( $_action->isRequestObject() ) {
                    if( $object ) {
                        $status = $this->getObjectWorkflowStatus( $object ) ;
                    } else {
                        /**
                         * @fixme maybe add debug code to check why no object ?
                         */
                        $status = $this->getRouteWorkflowStatus() ;
                    }
                } else {
                    $status = $this->getRouteWorkflowStatus() ;
                }

                $config = $this->admin_loader->getCurrentLoginSecurityAuthorize( $this->name ) ;
                if( !$config ) {
                    return false ;
                }
                if( !isset($config['workflow'][$status['name']]) ) {
                    return false ;
                }
                if( !isset($config['workflow'][$status['name']]['action'][$action]) ) {
                    return false ;
                }
            }
           
        }

        if( $_action->isPageAction() ) {
            if( $object ) {
                if( ! $this->page_one2one_map ) {
                    return false ;
                }
            } 
        }
        
        return $is_visiable ;
    }
    
    public function isPropertyVisiable($property_name, ActionCache $action , $object = null ) {
        
        if( isset($this->form_elements[$property_name]['auth']) && $this->form_elements[$property_name]['auth'] ) {
            $securityContext = $this->container->get('security.context');
            $user   = $securityContext->getToken()->getUser() ;
            $group  = $user->getUserGroup() ;
            if( $group ) {
                if( !$group->isPropertyVisiable( $this->name , $property_name, $action->getName() ) ) {
                    if( true === $this->form_elements[$property_name]['auth'] ) {
                        return false ;
                    }
                    return $securityContext->isGranted( $this->form_elements[$property_name]['auth'] ) ;
                }
            } else {
               if( !$securityContext->isGranted(\Symforce\UserBundle\Entity\User::ROLE_SUPER_ADMIN) )  {
                   return false ;
               }
            }
        }
        
        if( $this->workflow ) {
            if( $object ) {
                $status     = $this->getObjectWorkflowStatus( $object ) ;
            } else {
                if( ! $this->_route_workflow_status ) {
                    throw new \Exception("big error");
                }
                $status     =  $this->workflow['status'][ $this->_route_workflow_status ];
            }
            if( !isset($status['properties'][$property_name] ) ) {
                return false ;
            }
        }
        
        if( $property_name === $this->route_parent_property ) {
            
            if( $action->isListAction() ) {
                 return false ;
            }
            
            $securityContext = $this->container->get('security.context') ;
            if( !$securityContext->isGranted('ROLE_SUPER_ADMIN') ) {
                $config  = $this->admin_loader->getCurrentLoginSecurityAuthorize( $this->name ) ;
                if( $config && isset($config['property'][$property_name][ $action->getName() ] ) ) {
                    return true ;
                }
                return false ;
            }
        }
        
        if( isset($this->orm_map[$property_name]) ) {
            $map    =& $this->orm_map[$property_name] ;
            if( $map[1] ) {
                if ( $map[2] === \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE) {
                    return $this->admin_loader->auth( $map[1] , 'update' ) ;
                } else if ( $map[2] === \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY) {
                    return $this->admin_loader->auth( $map[1] , 'list' ) ;
                } else if ( $map[2] === \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE) {
                    return $this->admin_loader->auth( $map[1] , 'update' ) ;
                }
            }
        }
        
        
        if( isset($this->form_elements[$property_name]['parent_deps']) ) {
            if( $object ) {
                foreach($this->form_elements[$property_name]['parent_deps'] as $parent_property_name => $or ) {
                    $parent = $this->getReflectionProperty( $parent_property_name )->getValue( $object ) ;
                    if( !$parent ) {
                        return false ;
                    }
                    $parent_admin  = $this->admin_loader->getAdminByClass( $parent ) ;
                    foreach($or as $and ) {
                        $all_match  = true ;
                        foreach($and as $_parent_property => $_parent_property_values ) {
                            $_parent_property_value = $parent_admin->getReflectionProperty( $_parent_property )->getValue( $parent ) ;
                            if( false === $_parent_property_value ) {
                                $_parent_property_value = 0 ;
                            }else if( true === $_parent_property_value ) {
                                $_parent_property_value = 1 ;
                            }
                            $_parent_property_value = (string) $_parent_property_value ;
                            if( !in_array($_parent_property_value, $_parent_property_values ) ) {
                                $all_match = false ;
                            }
                        }
                        if( $all_match ) {
                            return true ;
                        }
                    } 
                }
                return false ;
            }
            if( $action->isListAction() && $this->route_parent_property ) {
                foreach($this->form_elements[$property_name]['parent_deps'] as $parent_property_name => $or ) {
                    if( $parent_property_name !== $this->route_parent_property ) {
                        continue ;
                    }
                    $parent_admin  = $this->getRouteParent();
                    $parent = $parent_admin->getRouteObject() ;
                    foreach($or as $and ) {
                        $all_match  = true ;
                        foreach($and as $_parent_property => $_parent_property_values ) {
                            $_parent_property_value = $parent_admin->getReflectionProperty( $_parent_property )->getValue( $parent ) ;
                            if( false === $_parent_property_value ) {
                                $_parent_property_value = 0 ;
                            }else if( true === $_parent_property_value ) {
                                $_parent_property_value = 1 ;
                            }
                            $_parent_property_value = (string) $_parent_property_value ;
                            if( !in_array($_parent_property_value, $_parent_property_values ) ) {
                                $all_match = false ;
                            }
                        }
                        if( $all_match ) {
                            return true ;
                        }
                    }
                    return false ;
                }
            }
        }
        
        return true ;
    }
    
    public function isPropertyReadonly($property_name, ActionCache $action, $object = null ){
        $securityContext = $this->container->get('security.context');
        $user   = $securityContext->getToken()->getUser() ;
        $group  = $user->getUserGroup() ;
        if( $group ) {
            if( $group->isPropertyReadonly( $this->name , $property_name, $action->getName() ) )  {
                return true ;
            }
        } else {
            if ( !$securityContext->isGranted('ROLE_SUPER_ADMIN') ) {
                return true ;
            }
        }
        return false ;
    }

}
