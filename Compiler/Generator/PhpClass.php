<?php

namespace Symforce\AdminBundle\Compiler\Generator;

class PhpClass extends \CG\Generator\PhpClass {

    /**
     * @var \Symforce\AdminBundle\Compiler\Generator\PhpWriter
     */
    protected $lazy_writer ;
    
    protected $lazy_properties  = array() ;
    
    /**
     * @param string $name
     * @return \Symforce\AdminBundle\Compiler\Generator\PhpMethod
     */
    public function addMethod( $name ) {
        $method  = new PhpMethod($name) ;
        $method
                ->setFinal(true)
                ->setVisibility('protected')
                ;
        $this->setMethod( $method ) ;
        return $method ;
    }

    /**
     * @param string $name
     * @return \Symforce\AdminBundle\Compiler\Generator\PhpMethod
     */
    public function getMethod($name) {
        if( !$this->hasMethod($name) ) {
            throw new \Exception ;
        }
        $methods = $this->getMethods() ;
        return $methods[$name] ;
    }

    /**
     * @param string $name
     * @param string $key
     * @param mixed $value
     * @return \Symforce\AdminBundle\Compiler\Generator\PhpClass
     */
    public function addLazyArray($name, $key, $value ) {
        if( !isset($this->lazy_properties[$name]) ) {
            $this->lazy_properties[$name]   = array() ;
        }
        if( isset($this->lazy_properties[$name][ $key ]) ) {
            if( is_array($this->lazy_properties[$name][ $key ]) && is_array($value) ) {
                $this->lazy_properties[$name][ $key ]  = array_merge( $this->lazy_properties[$name][ $key ] , $value  ) ;
            } else {
                throw new \Exception( sprintf( 'overwride lazy property for %s->%s[%s] ', $this->getName(), $name, $key ) );
            }
        } else {
            $this->lazy_properties[$name][ $key ]   = $value ;
        }
        return $this ;
    }

    /**
     * @return \Symforce\AdminBundle\Compiler\Generator\PhpWriter
     */
    public function getLazyWriter() {
        if( null === $this->lazy_writer ) {
            $method = $this->addMethod('__wakeup') ; 
            $this->lazy_writer  = $method->getWriter() ;
            
        }
        return $this->lazy_writer  ;
    }
    
    /**
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @param bool $_get
     * @param string $visibility
     * @param bool $_lazy
     * @return \Symforce\AdminBundle\Compiler\Generator\PhpProperty
     */
    public function addProperty($name, $value, $type = null , $_get = false, $visibility = 'protected', $_lazy = false ) {
        $property   = new PhpProperty($name) ;
        if( null === $type ) {
            $type   = is_object( $value ) ? get_class( $value ) : gettype( $value ) ;
        }
        $property
                ->setClass( $this )
                ->setDocblock('/** @var ' . $type . ' */')
                ->setVisibility($visibility)
                ->setDefaultValue($value)
                ->useGetter( $_get )
                ->setLazy( $_lazy )
                ;
        return $this ;
    }
    
    public function propertyEncode( $object ){
        if(is_object($object)) {
            throw new \Exception('can not encode object') ;
        } else if( is_array( $object) ) {
            return var_export($object, 1) ;
        } else {
            return json_encode($object) ;
        } 
    }

    public function writeCache() {

        static $_psr4_map   = null ;
        if( null === $_psr4_map ) {
            $_psr4_file = dirname( (new \ReflectionClass('Composer\\Autoload\\ClassLoader'))->getFileName() ) . '/autoload_psr4.php' ;
            if( !file_exists($_psr4_file) ) {
                throw new \Exception(sprintf("psr4 file(%s) not exits!", $_psr4_file)) ;
            }
            $_psr4_map   = include( $_psr4_file ) ;
        }

        $_class_file = null ;
        foreach($_psr4_map as $_namespace => $_namespace_dir ) if( !empty($_namespace_dir) ) {
            $_pos = strpos($this->getName(), $_namespace) ;
            if( 0 === $_pos ) {
                $_class_file = $_namespace_dir[0] . '/' . str_replace('\\', '/', substr( $this->getName(), strlen($_namespace) ) ) . '.php' ;
            }
        }
        if( !$_class_file ) {
            throw new \Exception(sprintf("can not resolve file for class(%s) by psr4 rule!", $this->getName())) ;
        }

        $shortName = pathinfo($_class_file, \PATHINFO_FILENAME );
        $namespace = $this->getNamespace() ;

        $writer = new \Symforce\AdminBundle\Compiler\Generator\PhpWriter();
        $writer
            ->writeln("<?php\n")
        ;

        if ( !empty($namespace) ) {
            $writer->writeln("namespace " . $namespace . ";\n") ;
        }
        
        $imports    = $this->getUseStatements() ; 
        
        foreach($imports as $alias => $use ) {
            $_alias = substr( $use, -1 - strlen($alias) );
            if( $_alias == '\\' . $alias ) {
                $writer->writeln(sprintf("use %s ;", $use));
            } else {
                $writer->writeln(sprintf("use %s as %s ;", $use, $alias));
            }
        }
        
        $writer
            ->writeln('')
            ->writeln('/**')
            ->writeln(' * This code has been auto-generated by the SymforceAdminBundule')
            ->writeln(' * Manual changes to it will be lost.')
            ->writeln(' */')
            ;
        
        if( $this->isAbstract() ) {
            $writer->write('abstract ');
        } else if( $this->isFinal() ) {
            $writer->write('final ') ;
        }
        
        $writer       
            ->write('class '.  $shortName ) ;
           
        if( $this->getParentClassName() ) {
            $writer->write(' extends ' . $this->getParentClassName() ) ;
        }
       
        $writer->writeln(' {')
            ->indent()
        ;
        
        $lazy_writer    = $this->getLazyWriter() ;
       
        foreach( $this->getProperties() as $property ) {
            $property->writeCache($lazy_writer, $writer) ;
        }
        
        foreach($this->lazy_properties as $name => $value ) {
            $writer->writeln("\npublic \${$name} = " . $this->propertyEncode($value)  . " ;") ;
            // $lazy_writer->writeln( '$this->' . $name . ' = ' .  . ' ; ' );
        }
        
        if( $this->hasMethod( '__wakeup' ) ) {
            $lazy_writer->writeln(  $this->getMethod('__wakeup')->getBody() ) ; 
            $this->getMethod('__wakeup')->setBody( $lazy_writer->getContent() ) ;
        } else {
            $this->setMethod(\CG\Generator\PhpMethod::create('__wakeup')
                ->setFinal(true)
                ->setVisibility('protected')
                ->setBody( $lazy_writer->getContent() )
            );
        }
        
        foreach( $this->getMethods() as $method ) {
            
            if( $method instanceof PhpMethod) {
                $method->flushLazyCode() ;
                $_body  = $method->getWriter()->getContent() ;
            } else {
                $_body  = $method->getBody() ;
            }
            
            $writer->write("\n") ;
            if( $method->getDocblock() ) {
                $writer->writeln( $method->getDocblock() ) ;
            }
            if( $method->isFinal() ) {
                $writer ->write('final ') ;
            }
            $writer
                    ->write( $method->getVisibility() ) 
                    ->write( ' function ' ) 
                    ->write( $method->getName() ) 
                    ;
            $ps = $method->getParameters()  ;
            if( empty($ps) ) {
                $writer->write('()') ;
            } else {
                $writer->writeln('(')->indent();
                foreach( $method->getParameters() as $i => $p) {
                    if( $p->getType() ) {
                        if( in_array( $p->getType(), array('mixed') ) ) {
                            $writer->write( '/** @var ' . $p->getType() . ' */') ;
                        } else {
                            $writer->write(  $p->getType() . ' ') ;
                        }
                    }
                    if( $p->isPassedByReference() ) {
                        $writer->write(' & ') ;
                    }
                    $writer
                            ->write(' $')
                            ->write( $p->getName() )
                            ;
                    if( $p->hasDefaultValue() ) {
                        $writer->write(' = ' .  json_encode( $p->getDefaultValue() ) ) ;
                    }
                    if( $i < count($ps) - 1 ) {
                        $writer->writeln(",");
                    } else {
                        $writer->write("\n");
                    }
                }
                
                $writer->writeln(')')->outdent();
            }
            
            $writer
                    ->writeln( '{' )
                        ->indent()
                        ->writeln( $_body )
                        ->outdent()
                    ->writeln("}")
                    ;
        }
        
        $writer
                ->outdent()
                ->writeln('}') ;
        
        $content    = $writer->getContent() ;

        /**
         * convert the fake php code
         * '#php{% $this->admin->trans("test.form.enabled.choices.no") %}'
         */
        $content    = preg_replace_callback( self::PHP_CODE , function($m){
            return stripslashes($m[1]) ;
        } , $content ) ;

        $_class_dir  = dirname($_class_file) ;
        if( !file_exists( $_class_dir) ) {
            if( !@mkdir( $_class_dir, 0755) ) {
                throw new \Exception( sprintf("mkdir(%s) error!", $_class_dir));
            }
        }
        
        \Dev::write_file( $_class_file, $content ) ;
        return $_class_file ;
    }
    
    
    public function flush(\Symforce\AdminBundle\Compiler\Generator $gen) {
        $this->writeCache() ;
    }
    
    const PHP_CODE = '/\'\#php\{\%\s(.+?)\s\%\}\'/s' ;
}