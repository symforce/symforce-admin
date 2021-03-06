<?php

namespace Symforce\AdminBundle\Compiler\MetaType\Form ;

abstract class Element extends \Symforce\AdminBundle\Compiler\MetaType\PropertyAbstract {
    
    /**
     * @var \Symforce\AdminBundle\Compiler\Generator\TransGeneratorValue 
     */
    public $label ;
    
    public $auth_node ;
    
    public $view = true ;
    
    /**
     * @var string 
     */
    public $group ;
    
    public $position ;
    
    // shared form property
    
    public $mapped ;
    
    public $error_bubbling ;
    
    public $default ;
    public $unique ;
    public $required ;
    public $not_blank ;
    public $read_only ;
    
    /**
     * @var Mopa\Invalid
     */
    public $invalid ;


    /* for mopa */
    
    /**
     * @var bool
     */
    public $label_render ;

    /**
     * @var Mopa\Help 
     */
    public $help ;
    
    /**
     *
     * @var Mopa\Widget
     */
    public $widget ;
    
    /**
     *
     * @var Mopa\Attr
     */
    public $attr ;
    
    /**
     *
     * @var Mopa\Wrap
     */
    public $wrap ;
    
    /**
     * @var array
     */
    public $show_on ;
    
    /**
     * @var array
     */
    public $parent_on ;
    
    // ================= other filed
    public $compile_meta_type ;
    public $compile_form_type ;
    public $compile_orm_type ;
    
    private $lazy_initialized ;

    public function lazyInitialize() {
        if( $this->lazy_initialized  ) {
            throw new \Exception('big error') ;
        }
        $this->lazy_initialized = true ;
        
        // add tranlation
        $this->tr_node   = $this->property_container->tr_node->getNodeByPath( $this->class_property ) ; 
        
        if( !$this->label ) { 
            $map    = $this->getPropertyDoctrineAssociationMapping() ;
            if( $map && $this->admin_object->generator->hasAdminClass( $map['targetEntity'] ) ) {
                $admin  = $this->admin_object->generator->getAdminByClass( $map['targetEntity'] ) ;
                $this->label     = $admin->getLabel() ;
            } else {
                $this->label     = $this->admin_object->generator->getTransValue( 'property', $this->class_property . '.label' ) ;
            }
        } else {
            $this->label     = $this->tr_node->createValue( 'label', $this->label ) ;
        }
        if( null !== $this->show_on ) {
            if( !is_array($this->show_on) ) {
                $this->throwError("show_on need be array, you set %s", gettype($this->show_on) );
            }
            $_or   = $this->show_on ;
            if( !\Dev::isSimpleArray($this->show_on) ) {
                $_or   = array( $_or ) ;
            }
            foreach($_or as  $_or_i => $_and ) {
                foreach($_and as $_property => $values ) {
                    if( !$this->property_container->hasProperty($_property) ) {
                        $this->throwError("show_on use property `%s` not form type", $_property );
                    }
                    $element    = $this->property_container->getProperty($_property)  ;
                    if( !($element instanceof Choice) && !($element instanceof Workflow) ){
                        $this->throwError("show_on use property `%s` is (%s) form type, exprect choice type", $_property, $element->compile_form_type );
                    }
                }
            }
            
            foreach($_or as $and_i => $and ) {
                foreach($and as $when_i => $values ) {
                    if( !is_array($values) ) {
                        $values = explode(',', trim($values) ) ;
                    }
                    foreach($values as $_value_i => $when_value ) {
                        $values[ $_value_i ] = (string) trim($when_value) ;
                    }
                    $_or[$and_i][$when_i] = $values ;
                }
            }
            
            $this->show_on  = $_or ;
        }
        if( null !== $this->parent_on ) {
            $on = $this->parent_on ;
            foreach($on as $parent_property_name => $_or ) {
                if( !\Dev::isSimpleArray($_or) ) {
                    $_or   = array( $_or ) ;
                } 
                if( !$this->property_container->entity->reflection->hasProperty( $parent_property_name ) ) {
                    $this->throwError("parent_on use property `%s` not exists", $parent_property_name );
                }
                if( !$this->property_container->entity->orm_metadata->hasAssociation( $parent_property_name ) ) {
                    $this->throwError("parent_on use property `%s` not orm association", $parent_property_name );
                }
                $map    = $this->property_container->entity->getPropertyDoctrineAssociationMapping( $parent_property_name ) ;
                
                if( $map['type'] === \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY ) {
                    $this->throwError("parent_on use property `%s` with one to many orm map", $parent_property_name );
                }
                
                if( $map['type'] === \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY ) {
                    $this->throwError("parent_on use property `%s` with many to many orm map", $parent_property_name );
                }
                
                $admin  = $this->admin_object->generator->getAdminByClass( $map['targetEntity'] ) ; 
                
                foreach($_or as  $_or_i => $_and ) {
                    foreach($_and as $_property => $values ) {
                        if( ! $admin->reflection->hasProperty($_property) ) {
                            $this->throwError("parent_on use property `%s` not exists", $parent_property_name, $_property );
                        }
                    }
                }
                
                foreach($_or as $and_i => $and ) {
                    foreach($and as $when_i => $values ) {
                        if( !is_array($values) ) {
                            $values = explode(',', trim($values) ) ;
                        }
                        foreach($values as $_value_i => $when_value ) {
                            $values[ $_value_i ] = (string) trim($when_value) ;
                        }
                        $_or[$and_i][$when_i] = $values ;
                    }
                }
                
                $on[ $parent_property_name ] = $_or ;
            }
            $this->parent_on    = $on ;
        }
    }
    
    public function set_auth($value){
        if( true === $value ) {
            $this->auth_node = $value ;
        } else if( 'super' === $value ) {
            $this->auth_node = \Symforce\UserBundle\Entity\User::ROLE_SUPER_ADMIN ;
        } else if( 'admin' === $value ) {
            $this->auth_node = \Symforce\UserBundle\Entity\User::ROLE_ADMIN ;
        } else {
            $this->throwPropertyError("auth", " can not set to (%s)", var_export($value,1) );
        }
    }
    
    public function set_position( $position ) {
        $this->position = (int) $position ;
    }
    
    public function set_invalid($value){
        $this->invalid  = new Mopa\Invalid($value) ;
    }
    
    public function set_help( $value ) {
        $this->help =  new Mopa\Help( $value ) ;
    }
    
    public function set_widget( $value ) {
        $this->widget =  new Mopa\Widget( $value ) ;
    }
    
    public function set_attr( $value ) {
        $this->attr =  new Mopa\Attr( $value ) ;
    }
    
    public function set_wrap( $value ) {
        $this->wrap =  new Mopa\Wrap( $value ) ;
    }
    
    public function set_group( $value ) {
        if( preg_match('/[\W]/', $value) ) {
            $this->throwError("group(%s) invalid", $value);
        }
        $this->group =  $value ;
    }

    /**
     * @inherit
     */
    public function getFormOptions(){
        
        $options    = array(
            'sf_form_type'  => $this->compile_form_type ,
            'sf_form_meta'  => $this->compile_meta_type ,
            'constraints' => array() ,
            'attr' => array() ,
        );
        
        if( !$this->label || !$this->label instanceof \Symforce\AdminBundle\Compiler\Generator\TransGeneratorValue ) {
            throw new \Exception(
                    sprintf("label should be \Symforce\AdminBundle\Compiler\Generator\TransGeneratorValue, but get `%s` for `%s->%s`", 
                            is_object($this->label) ? get_class($this->label) : gettype($this->label) ,
                            $this->admin_object->class_name, 
                            $this->class_property
                        ));
        }
        
        $options['label']  = $this->label->getPhpCode() ;
        
        if( null !== $this->mapped ) {
            $options['mapped']  = $this->mapped ;
        }
        
        if( null !== $this->error_bubbling ) {
            $options['error_bubbling']  = $this->error_bubbling ;
        }
        
        if( null !== $this->required ) {
            $options['required']  = $this->required ;
        } else {
            $options['required']  = !$this->isNullable() ;
        }
        
        if( null !== $this->read_only ) {
            $options['read_only']  = $this->read_only ;
        } else if ( $this->auth_node ) {
            $options['read_only']  = $this->compilePhpCode('$this->isPropertyReadonly("' . $this->class_property . '", $action, $object)') ;
        }
        
        if( null !== $this->not_blank ) {
            $options['not_blank']  = $this->not_blank ;
        }
        
        if( null !== $this->label_render ) {
            $options['label_render']  = $this->label_render ;
        }
        
        if( null !== $this->invalid ) {
            
        }
        
        if( null !== $this->help ) {
            
        }
        
        if( null !== $this->widget ) {
            
        }
        
        if( null !== $this->attr ) {
            
        }
        
        if( null !== $this->wrap ) {
           
        }
        
        if( null !== $this->show_on ) {
            /**
             * @todo add php code handle
             */
            $options['sf_form_dynamic'] = $this->show_on ;
        }
        return $options ;
    }

    
    public function compileForm() {
        
        $add_tr = function($name, $value) {
            if( null !== $value ) {
                 $this->tr_node->set( $name , $value ) ;
            }
        } ;
        
        if( $this->invalid ) {
            $add_tr( 'invalid_message', $this->invalid->getMessage() ) ;
        }
        
        if( $this->help ) {
            $add_tr( 'help_inline', $this->help->getInline() ) ;
            $add_tr( 'help_block', $this->help->getBlock() ) ;
            $add_tr( 'help_label', $this->help->getLabel() ) ;
        }
        
        if( $this->widget ) {
            $add_tr( 'widget_prefix', $this->widget->getPrefix() ) ;
            $add_tr( 'widget_suffix', $this->widget->getSuffix() ) ;
            
            if( $this->widget->getTooltip() ) {
                $add_tr( 'widget_tooltip_title', $this->widget->getTooltip()->getTitle() ) ;
            }
            
            if( $this->widget->getPopover() ) {
                $add_tr( 'widget_popover_title', $this->widget->getPopover()->getTitle() ) ;
                $add_tr( 'widget_popover_content', $this->widget->getPopover()->getContent() ) ;
            }
            
            if( $this->widget->getAddBtn() ) {
                $add_tr( 'widget_add_btn', $this->widget->getAddBtn()->getLabel() ) ;
            }
            
            if( $this->widget->getAddBtn() ) {
                $add_tr( 'widget_remove_btn', $this->widget->getRemoveBtn()->getLabel() ) ; 
            }
            
        }
        
        if( $this->attr ) {
            $add_tr( 'placeholder', $this->help->getPlaceholder() ) ;
        }
        
        $admin_class    = $this->admin_object->getCompileClass() ;
        
        $form_elements_value    = array(
            'type'   => $this->compile_form_type ,
            'auth'   => $this->auth_node ,
        ) ;
        if( $this->show_on ) {
            $form_elements_value['show']    =  $this->show_on ;
        }
        if( $this->parent_on ) {
            $form_elements_value['parent_deps']    =  $this->parent_on ;
        }
        
        $admin_class->addLazyArray( 'form_elements',  $this->class_property ,  $form_elements_value ) ; 
        
        if( $this->isUniqueField() || $this->unique && $this->admin_object->orm_metadata->hasField($this->class_property) ) {
            $validator_writer = $this->admin_object->getCompileValidatorWriter() ;
            $code   = sprintf('new \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity(array( "fields" => "%s", ',  $this->class_property ) ;
            if( is_string($this->unique) ) {
                $value  = $this->tr_node->createValue( 'unique' , $this->unique ) ;
                $code   .= sprintf('"message" => %s,', $value->getNakePhpCode() ) ;
            }
            $code   .= '))' ;
            $validator_writer->writeln(' $metadata->addConstraint(' .  $code . ');');
        }
        
        $writer     = $this->admin_object->form->getCompileFormWriter() ;
        
        $options    = $this->getFormOptions() ;
        
        if( $this->admin_object->property_slug_name === $this->class_property ) {
            $options['constraints'][] = $this->compilePhpCode( 'new \Symforce\AdminBundle\Form\Constraints\Slug( array( "create" => $action->isCreateAction()) )' )  ;
            $options['required']  = false ;
        }
        
        foreach($options as  $key => & $_option ) {
            if( is_array($_option) && empty($_option) ) {
                unset($options[$key]) ;
            }
        }
        
        $writer
                ->writeln('if( ' . var_export( $this->class_property, 1 ) . ' === $property ){')
                ->indent();
        $this->compileFormOption($writer, $options);
        $writer->outdent()
                ->writeln('}')
            ;
    } 
    
    public function compileFormOption(\Symforce\AdminBundle\Compiler\Generator\PhpWriter $writer, array & $options) {
        $writer->writeln('return ' . var_export( $options , 1 ) . ';' ) ;
    }
    
    
    public function compileActionForm(\Symforce\AdminBundle\Compiler\MetaType\Action\AbstractAction $action, $builder = '$builder', $admin = '$this', $object = '$object', $parent_property = null ){
        $writer = $action->getCompileFormWriter() ;
        $writer->write('if('. $admin .'->isPropertyVisiable("'. $this->class_property . '", $action, '. $object .')) { $this->buildFormElement($controller, ' . $builder . ', ' . $admin . ', $action, ' .  $object . ', "'. $this->class_property . '", ' . var_export( $parent_property, 1 ) . ');');
        /*
        if( $this instanceof Bool ) {
             // hack to fix the bool=false radio not get auto checked
            $writer->write( sprintf('%s->get("%s")->addViewTransformer(new \Symforce\AdminBundle\Form\DataTransformer\BoolTransformer());', $builder, $this->class_property) ) ;
        }
        */
        $writer->writeln('}');
    }
    
    public function throwError() {
        $args   = func_get_args() ;
        $argv   = count($args) ;
        if( !$argv ) {
            $msg    = '' ;
        } else if( $argv == 1 ) {
            $msg    = $args[0] ;
        } else {
            $msg    = call_user_func_array('sprintf', $args ) ;
        }
        // $this->_throw_append_message .= sprintf(' from (%s->%s)', $this->admin_object->class_name, $this->class_property ) ;
        parent::throwError($msg);
    }
}
