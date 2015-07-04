<?php

namespace Symforce\AdminBundle\Compiler\Cache ;

trait AdminForm {
    
    public $auth_properties ;
    
    public function buildFormElement(\Symfony\Bundle\FrameworkBundle\Controller\Controller $controller, \Symfony\Component\Form\FormBuilder $builder, \Symforce\AdminBundle\Compiler\Cache\AdminCache $admin, \Symforce\AdminBundle\Compiler\Cache\ActionCache $action, $object, $property_name, $parent_property ) {
       
        if( $object ) {
            if( !($object instanceof $admin->class_name) ) {
                throw new \Exception ;
            }
            if( $admin->workflow ) {
                if( $admin !== $this ) {
                    throw new \Exception ;
                }
                $status = $admin->getObjectWorkflowStatus( $object ) ;
                if( !isset($status['properties'][$property_name]) ) {
                    return ;
                }
                $flag   = $status['properties'][$property_name] ;
                $readable   = \Symforce\AdminBundle\Compiler\MetaType\Admin\Workflow::FLAG_VIEW & $flag ;
                $editable   = \Symforce\AdminBundle\Compiler\MetaType\Admin\Workflow::FLAG_EDIT & $flag ;
                if( \Symforce\AdminBundle\Compiler\MetaType\Admin\Workflow::FLAG_AUTH & $flag ) {
                    if( $readable || $editable ) {
                        throw new \Exception ;
                    }
                    $securityContext = $this->container->get('security.context');
                    $user   = $securityContext->getToken()->getUser() ;
                    $group  = $user->getUserGroup() ;
                    if( $group ) {
                        $flag   = $group->getWorkflowPropertyVisiable($this->name, $property_name, $status['name']); 
                        if( '1' === $flag ) {
                            $editable   = true ;
                        } else if ( '2' === $flag ) {
                            $readable   = true ;
                        }
                    }
                }
                if( $readable ) {
                    $options    = array(
                        
                    ) ;
                    $_options    = $admin->getFormBuilderOption( $property_name, $action, $object ) ;
                    if( $_options ) {
                        $options['label'] = $_options['label']; 
                    }
                    $builder->add( $property_name, 'appview', $options ) ;
                    return ;
                }
                if( ! $editable ) {
                    return ;
                }
            }
        }
        $options    = $admin->getFormBuilderOption( $property_name, $action, $object ) ;
        $type       = $options['appform_type'] ;
        if( isset($options['read_only']) && $options['read_only'] ) {
            if( in_array($type, array('appowner', 'appentity', 'appworkflow', 'choice', 'checkbox', 'appfile', 'appimage', 'apphtml', 'money' )) ) {
                $options    = array(
                    
                ) ;
                $_options    = $admin->getFormBuilderOption( $property_name, $action, $object ) ;
                if( $_options ) {
                    $options['label'] = $_options['label']; 
                }
                $builder->add( $property_name, 'appview', $options ) ;
                return ;
            }
        }
        
        if( isset($options['required']) && $options['required'] ) { 
            if( !isset($options['constraints']) ) {
                $options['constraints'] = array(  new \Symfony\Component\Validator\Constraints\NotBlank() ) ;
            } else {
                $find_not_blank = false ;
                foreach($options['constraints'] as $_constraint ) {
                    if( $_constraint instanceof \Symfony\Component\Validator\Constraints\NotBlank ) {
                           $find_not_blank = true ;
                    }
                }
                if( !$find_not_blank ) {
                    $options['constraints'][]   = new \Symfony\Component\Validator\Constraints\NotBlank() ;
                }
            }
        }

        /**
         * @FIXME since symfony 2.7, without this hack radio(value=0) will not get checked
         */
        if( 'bool' == $options['appform_meta'] ) {
            if( $object ) {
                $options['data'] = $admin->getReflectionProperty($property_name)->getValue( $object ) ? 1 : 0 ;
            } else {
                $options['data'] = 0 ;
            }
        }

        $this->adjustFormOptions($object, $property_name, $options);
        $subscribers = null ;
        if( isset($options['subscribers']) ) {
            $subscribers = $options['subscribers'] ;
            unset($options['subscribers']) ;
        }
        
        $builder->add( $property_name, $type, $options ) ;
        if( $subscribers ) {
            $_builder   = $builder->get( $property_name ) ;
            foreach($subscribers as $subscriber ){
                $events = $subscriber->getSubscribedEvents() ;
                foreach($events as $_event => $method  ) {
                    $_builder->addEventListener($_event, array($subscriber, $method) ) ;
                }
            }
        }
    }
    
    public function adjustFormOptions($object, $property, array & $options){
        
    }


    public function addFormElement($builder, $property_name, array $_options = null , $type = null ){
        $options    = $this->getFormBuilderOption( $property_name ) ;
        if( !$options ) {
            throw new \Exception( sprintf("%s->%s not exists", $this->name , $property_name) ) ;
        }
        if( null === $type ) {
            $type   = $options['appform_type'] ;
        } else {
            if( $type === 'appview' ) {
                $options    = array(
                    'label' => $options['label'] ,
                );
            }
        }
        $subscribers = null ;
        if( isset($options['subscribers']) ) {
            $subscribers = $options['subscribers'] ;
            unset($options['subscribers']) ;
        }
        
        $constraints    = null  ;
        if( isset($options['constraints']) && isset($_options['constraints']) ) {
            $constraints    = array() ;
            foreach($options['constraints'] as $constraint ) {
                $constraints[get_class($constraint) ] = $constraint ;
            }
            foreach($_options['constraints'] as $constraint ) {
                $constraints[get_class($constraint) ] = $constraint ;
            }
            unset($options['constraints']) ;
            unset($_options['constraints']) ;
        }
        
        if( $_options ) {
            \Dev::merge($options , $_options ) ;
        }
		
        if( isset($options['required']) && $options['required'] ) {
            $constraint = new \Symfony\Component\Validator\Constraints\NotBlank();
            if( !$constraints ) $constraints = array() ;
            $constraints[ get_class($constraint) ] = $constraint ;
        }
		
        if( $constraints ) {
            $options['constraints'] = array_values($constraints) ;
        }
        $builder->add( $property_name, $type, $options ) ;
        if( $subscribers ) {
            $_builder   = $builder->get( $property_name ) ;
            foreach($subscribers as $subscriber ){
                $events = $subscriber->getSubscribedEvents() ;
                foreach($events as $_event => $method ) {
                    $_builder->addEventListener($_event, array($subscriber, $method) ) ;
                }
            }
        }
    }
    
    public function buildForm(\Symfony\Bundle\FrameworkBundle\Controller\Controller $controller, \Symfony\Component\Form\FormBuilder $builder, ActionCache $action, $object) {
        if( !($object instanceof $this->class_name) ) {
            throw new \Exception  ;
        }
        $this->buildDynamicForm($builder,$object) ;
    }

    public function buildDynamicForm(\Symfony\Component\Form\FormBuilder $builder,  $object){

        $elements       = array() ;
        $requirments    = array() ;
        $this->checkFormBuilderDeps($builder, $elements, $requirments) ;

        foreach($requirments as $property_name => $value ) {
            $value  = $this->getReflectionProperty( $property_name )->getValue($object) ;
            if( is_bool($value) || null === $value ) {
                $value  =   $value ? '1' : '0' ;
            } else if( is_numeric($value) ){
                $value  = (string) $value ;
            }
            $requirments[$property_name]    = $value ;
        }

        $builder->add('sf_admin_form_dynamic_values', 'hidden', array(
            'mapped'   => false ,
            'data'  => json_encode($requirments) ,
        ));

        $builder->add('sf_admin_form_dynamic_deps', 'hidden', array(
            'mapped'   => false ,
            'data'  => json_encode($elements) ,
        ));
    }


    private function checkFormBuilderDeps(\Symfony\Component\Form\FormBuilder $builder, array & $elements,  array & $requirments){

        if( $builder->getCompound() ) {
            $type   = $builder->getType() ;
            if( $type instanceof \Symfony\Component\Form\Extension\DataCollector\Proxy\ResolvedTypeDataCollectorProxy ) {
                $type   = $type->getInnerType() ;
            }
            if( !$type instanceof \Symfony\Component\Form\Extension\Core\Type\ChoiceType ) {
                foreach($builder->all() as $child) {
                    $this->checkFormBuilderDeps( $child, $elements,  $requirments);
                }
            }
        }

        $options    = $builder->getOptions() ;
        if( isset($options['dynamic_show_on']) ) {
            foreach($options['dynamic_show_on'] as $and ) {
                foreach($and as $_name => $values) {
                    $requirments[$_name]  = true ;
                }
            }
            $elements[ $builder->getName() ] = $options['dynamic_show_on'] ;
        }
    }

    public function getChoiceText( $name, $value ) {
        if( null === $this->translator ) {
            $this->translator   = $this->container->get('translator') ;
        }
        if( $value instanceof $this->class_name ) {
            $value = $this->getReflectionProperty( $name )->getValue( $value ) ;
        }
        if( !isset($this->form_choices[ $name ][ $value ]) ) {
            return $value ; 
        }
        $path   = $this->form_choices[ $name ][ $value ] ;
        return $this->translator->trans( $path[0], array(),  $path[1] ? $this->app_domain : $this->tr_domain ) ;
    }
    
    public function getChoicesText( $name, $value ) {
        if( null === $this->translator ) {
            $this->translator   = $this->container->get('translator') ;
        }
        if( !$value instanceof $this->class_name ) {
            throw new \Exception ;
        }
        $values = $this->getReflectionProperty( $name )->getValue( $value ) ;
        $_values = array() ;
        foreach($values as $value ) {
            $path   = $this->form_choices[ $name ][ $value ] ;
            $_value = $this->translator->trans( $path[0], array(),  $path[1] ? $this->app_domain : $this->tr_domain ) ;
            $_values[ $value ] = $_value ;
        }
        return join(', ', $_values ) ;
    }
    
    public function getFormOption($name) {
        if( isset($this->form_elements[$name]) ) {
            return $this->form_elements[$name] ;
        }
    }
    
    public function getPropertyLabel($name) {
        if( isset($this->properties_label[$name]) ) {
            $config = $this->properties_label[$name] ; 
            return $this->trans( $config[0] , null, $config[1]  );
        }
        return $name ;
    }
    
    
    public function isFieldVisiable($property_name, $object ) {
        if( !isset($this->form_elements[$property_name]) ) {
            return true ;
        }
        if( !isset($this->form_elements[$property_name]['show']) ) {
            return true ;
        }
        foreach($this->form_elements[$property_name]['show'] as  $and_i => $and ) {
            
            $visiable   = true ;
            foreach($and as $property => $values ) {
                if( ! $this->isFieldVisiable( $property , $object )) {
                    $visiable   = false ;
                    break ;
                }
                $value = $this->getReflectionProperty($property)->getValue( $object ) ;
                if( false === $value ) {
                    $value = 0 ;
                } else if( true === $value ) {
                    $value = 1 ;
                }
                if( !in_array($value, $values ) ) {
                    $visiable   = false ;
                    break ;
                }
            }
            if( $visiable ) {
                return true ;
            }
        }
    }
}