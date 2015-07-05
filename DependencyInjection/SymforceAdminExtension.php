<?php

namespace Symforce\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;

use Symfony\Component\Yaml\Parser as YamlParser ;


// use Symfony\Component\PropertyAccess\PropertyAccess ;


class SymforceAdminExtension extends Extension
{
    /**
     *
     * @var YamlParser 
     */
    private $yamlParser ;
    
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('form_services.yml');
        $loader->load('routing_dynamic.yml');
        
        $processor = new Processor();
        $configs = $processor->processConfiguration( new Configuration() , $configs);
        
        $this->yamlParser = new YamlParser() ;
        
        //$access = PropertyAccess::getPropertyAccessor() ;


        $this->setForm($configs, $container);
        $this->setEntityLoader($configs, $container ) ;

        $this->setParameters($container, 'sf_routing', $configs['routing'] );

        $this->setParameters($container, 'sf.admin.route', $configs['admin']['route'] );
        unset($configs['admin']['route'] ) ;
        $this->setParameters($container, 'sf.admin', $configs['admin'] );

        if( !$container->hasParameter('mopa_bootstrap.form.templating') ||
                "MopaBootstrapBundle:Form:fields.html.twig" == $container->getParameter('mopa_bootstrap.form.templating') 
        ) {
            // $container->setParameter('mopa_bootstrap.form.templating', 'SymforceAdminBundle:Form:fields.html.twig' ) ;
        }
    }
    
    private function setParameters(ContainerBuilder $container, $path, array $list, $overwrite = true ){
        foreach($list as $key => $value ) {
            $_key   = $path . '.' . $key ;
            if( $overwrite && $container->hasParameter($_key) ) {
                continue ;
            }
            $container->setParameter( $_key ,  $value );
        }
    }
    
    private function setEntityLoader(array & $configs, ContainerBuilder $container){

         $generator  = $container->getDefinition('sf.admin.generator') ;
         
         if( isset($configs['admin']['menu']) ) {
             $generator->replaceArgument(2, $configs['admin']['menu'] ) ;
             unset($configs['admin']['menu']) ;
         }
         
         if( isset($configs['admin']['dashboard']) ) {
             $generator->replaceArgument(3, $configs['admin']['dashboard'] ) ;
             unset($configs['admin']['dashboard']) ;
         }
         
         $admin_loader   = $container->getDefinition('sf.admin.loader') ;
         $route_loader   = $container->getDefinition('sf.route.loader') ;
         
         $cache_dir = $container->getParameter('kernel.cache_dir') ;
         $admin_cache_file = $cache_dir . '/SymforceLoaderAdminCache.php' ;
         $admin_expired_file = $cache_dir . '/SymforceLoaderExpiredCache.php' ;
         $admin_route_file = $cache_dir . '/SymforceLoaderRouteCache.php' ;
         if( isset($configs['cache']) ) {

              unset($configs['cache']) ;
         }

         $admin_loader->replaceArgument(1, $admin_cache_file) ;
         $admin_loader->replaceArgument(2, $admin_expired_file ) ;
         $generator->replaceArgument(4, $admin_cache_file) ;
         $generator->replaceArgument(5, $admin_expired_file) ;
         $route_loader->replaceArgument(1, $admin_route_file) ;

         
         $locale = $container->getParameter('locale') ;
         if(  !isset($configs['language'][ $locale ] ) ) {
             throw new \Exception(sprintf("default locale `%s` is not find in sf.admin.language: %s", $locale, json_encode( $configs['language'] ) ) );
         }
         $locale_listener = $container->getDefinition('sf.locale.listener') ;
         $locale_listener->replaceArgument(1, $locale ) ;
         $locale_listener->replaceArgument(2, $configs['language'] ) ;
         unset( $configs['language'] ) ;
    }
    
    private function setForm(array & $configs, ContainerBuilder $container) {
        $config   = $this->yamlParser->parse(file_get_contents( __DIR__.'/../Resources/config/form.yml' )) ;
        $this->merge_recursive($config, $configs['form'] );  
        
        $form_factory  = $container->getDefinition('sf.form.factory') ;
        $form_factory->replaceArgument(2, $config['type'] ) ;
        
        $ignored    = array() ;
        foreach($config['ignored_route'] as $bundule => $controllers ) {
            if( !$controllers ) {
                $ignored[]  = '^' . $bundule . '\:' ;
            } else {
                if(is_string($controllers) ) {
                    $controllers = preg_replace('/\s/', '', $controllers) ;
                    $controllers = preg_split ('/\\,+/', $controllers ) ;
                }
                foreach($controllers as $controller ) {
                    $ignored[]  = '^' . $bundule . '\:' . preg_quote($controller)  . '\:' ;
                }
            }
        } 
        $container->setParameter('sf.form.route.ignored', '/' . join('|', $ignored ) . '/' ) ;
        
        $ignored    = array() ;
        foreach($config['ignored_template'] as $bundule => $controllers ) {
            if( !$controllers ) {
                $ignored[]  = '^' . $bundule . '$' ;
            } else {
                if(is_string($controllers) ) {
                    $controllers = preg_replace('/\s/', '', $controllers) ;
                    $controllers = preg_split ('/\\,+/', $controllers ) ;
                }
                foreach($controllers as $controller ) {
                    $ignored[]  = '^' . $bundule . '\:' . preg_quote($controller)  . '\W' ;
                }
            }
        }
        $container->setParameter('sf.form.template.ignored', '/' . join('|', $ignored ) . '/' ) ;
    }
    
    private function merge_recursive( array & $a1, array & $a2){
        foreach( $a2 as $key => & $value ) {
            if( !isset($a1[$key]) ) {
                $a1[$key]   = $value ;
            } else {
                if( is_array($value) ) {
                    if( !is_array($a1[$key]) ) {
                        $a1[$key]   = $value ; 
                    } else {
                        $this->merge_recursive($a1[$key], $value);
                    }
                } else {
                   $a1[$key]   = $value ; 
                }
            }
        }
    }

    public function getAlias()
    {
        return 'symforce_admin';
    }
}
