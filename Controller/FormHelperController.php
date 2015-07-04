<?php

namespace Symforce\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; 
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse ;

use Symforce\AdminBundle\Entity\File ;

/**
 * @author loong
 * @Route("/form")
 */
class FormHelperController extends Controller {
    
    /**
     * @return \Symforce\AdminBundle\Compiler\Loader\AdminLoader
     */
    private function getLoader() {
        return $this->container->get('sf.admin.loader') ;
    }
    
    /**
     * @Route("/tree/{admin_name}/{parent_id}/{selected_id}/{deep}", name="sf_form_tree", requirements={"admin_name"="[\w\_]+", "parent_id"="\d+", "selected_id"="\d+", "deep"="\d+"})
     * @Template()
     */
    public function treeAction(Request $request, $admin_name, $parent_id = 0 , $selected_id = 0, $deep = 0 )
    { 
        $admin = $this->getLoader()->getAdminByName($admin_name) ;
        
        if( !$admin->tree ) {
            throw new \Exception(sprintf("admin `%s` is not tree", $admin_name));
        }
        
        if( !isset($admin->tree['children']) || !isset($admin->tree['leaf'] )){
            throw new \Exception(sprintf("admin `%s` tree no children or leaf property", $admin_name));
        }
        
        if( $selected_id ) {
            $parents    = array() ;
            $object = $admin->getObjectById($selected_id) ;
            if( !$object ) {
                throw new \Exception(sprintf("admin `%s` id(%s) not exists", $admin_name, $id ));
            }
            $parents[]  = $selected_id ;
            for( $_parent =  $admin->getReflectionProperty( $admin->tree['parent'])->getValue( $object) ; $_parent ; $_parent =  $admin->getReflectionProperty( $admin->tree['parent'])->getValue( $_parent ) ){
                array_unshift($parents, $admin->getId($_parent) ) ;
            }
        }
        
        $node_id    = 'sf_form_tree_' . $admin_name . '_' ;
        
        $fn = function( $parent_id ) use($admin, $node_id, $selected_id, $deep ) {
            $dql    = sprintf("SELECT a FROM %s a WHERE a.%s=%d", $admin->getClassName(), $admin->tree['parent'] , $parent_id );
            if( !$parent_id ) {
                $dql    .= sprintf(" OR a.%s IS NULL", $admin->tree['parent']  );
            }
            $em     = $admin->getManager();
            $query  = $em->createQuery($dql);
            $list   = $query->getResult() ;
            $_list = array() ;
            
            foreach($list as $o ) {
                $name   = $admin->string( $o ) ;
                $id     = $admin->getId( $o ) ;
                
                $url =  $this->generateUrl('sf_form_tree', array(
                    'admin_name'    => $admin->getName() ,
                    'parent_id'    => $id ,
                    'selected_id'  => $selected_id ,
                    'deep'  => $deep + 1 
                ) ) ;
                
                $node = array(
                    'id'  =>  $node_id . $id ,
                    'parent'  => $parent_id ? $node_id . $parent_id : '#' ,
                    'text'  => $name ,
                    'a_attr'    => array(
                        'url'  => $url ,
                    ) ,
                );
                
                $leaf   = $admin->getReflectionProperty( $admin->tree['leaf'] )->getValue( $o ) ;
                if( !$leaf ) {
                    $children = $admin->getReflectionProperty( $admin->tree['children'] )->getValue( $o ) ;
                    if( $children->count() ) {
                        $node['children']   = true ;
                    }
                }
                
                $_list[ $id ] = $node ;
            }
            return $_list ;
        };
        
        $tree   = $fn( $parent_id ) ;
        
        if( $selected_id && isset($parents[$deep]) ) {
            $_selected_parent_id = $parents[ $deep ] ;
            if( isset($tree[$_selected_parent_id]) ) {
                if( $_selected_parent_id == $selected_id ) {
                    $tree[$_selected_parent_id]['state']    = array(
                        'selected'  => true ,
                    );
                } else {
                    $tree[$_selected_parent_id]['state']    = array(
                        'opened'  => true ,
                    );
                }
            }
        }
        
        return new JsonResponse ( array_values($tree) ) ;
    }
    
    
    /**
     * @Route("/routes/{default_route_name}",  name="sf_form_route")
     * @Template()
     */
    public function routeAction(Request $request, $default_route_name = null )
    { 
        $routes = $this->container->get('router')->getRouteCollection();
        $nameParser = $this->container->get('controller_name_converter') ;
        $ignored    = $this->container->getParameter('sf.form.route.ignored') ;
        
        $maps   = array() ;
        foreach ($routes as $name => $route) {
            
            if ( !$route->hasDefault('_controller') || $route->hasDefault('_sf_route_name') ) {
                continue ;
            }
            if( in_array( 'GET', $route->getMethods() ) ) {
                continue;
            }
            $controller = $route->getDefault('_controller') ;
            try {
                $action =  $nameParser->build( $controller ) ;
            } catch (\InvalidArgumentException $e) {
                continue ;
            }
            if( preg_match($ignored, $action) ) {
                continue;
            }
            $o = explode(':', $action ) ;
            $maps[ $o[0] ][ $o[1] ][$name] = $route ;
        }
        
        $tree   = array() ;
        foreach($maps as $bundule => $controllers ) {
            $_bundule   = array(
                'text'  => $bundule , 
                'state' => array(
                    'opened'    => false ,
                    'disabled'  => true ,
                ),
                'children'  => array() ,
            );
            foreach($controllers as $controler => $routes ) {
                $_controler = array(
                    'text'  => $controler , 
                    'state' => array(
                        'opened'    => false ,
                        'disabled'  => true ,
                    ),
                    'children'  => array() ,
                );
                foreach($routes as $route_name => $route ) {
                    $path   = $route->getPath() ;
                    $_route = array(
                        'text'  => $route_name , 
                        'li_attr'  => array(
                                'title' =>  $route->getPath() ,
                            ),
                    );
                    if( $route_name === $default_route_name ) {
                        $_controler['state']['opened']   = true ;
                        $_bundule['state']['opened']   = true ;
                        $_route['state']['selected']   = true ;
                    }
                    $_controler['children'][] = $_route ;
                }
                $_bundule['children'][] = $_controler ;
            }
            $tree[] = $_bundule ;
        }
        
        return new JsonResponse ( array_values($tree) ) ;
    }
    
    /**
     * @Route("/templates/{default_name}",  name="sf_form_template")
     * @Template()
     */
    public function templateAction(Request $request, $default_name = null )
    { 
        $ignored    = $this->container->getParameter('sf.form.template.ignored') ;
        
        $bundles = $this->container->getParameter('kernel.bundles') ;
        $maps   = array() ;
        foreach($bundles as $bundle_name => $bundle_class ) {
            if( preg_match($ignored, $bundle_name) ) {
                continue;
            }
            $rc = new \ReflectionClass( $bundle_class ) ;
            $views_dir = dirname( $rc->getFileName() ) . '/Resources/views/' ;
            
            if( !file_exists($views_dir) ) {    
                continue ;
            }
            
            $finder  = \Symfony\Component\Finder\Finder::create();
            $finder->files()->name('*.twig')->depth(1)->in( $views_dir );
            
            foreach($finder as $file) {
                $dir    = $file->getRelativePath();
                $filename   = $file->getFileName() ;
                if( $dir ) {
                    if( false !== strpos($dir, '/') ) {
                        continue ; 
                    } 
                    $path   = $bundle_name . ':' . $dir . ':' . $filename ;
                    if( preg_match($ignored, $path) ) {
                        continue;
                    }
                    $maps[ $bundle_name ]['dirs'][ $dir ][$filename] = $path ;
                } else {
                    $path   = $bundle_name . '::' . $filename ;
                    if( preg_match($ignored, $path) ) {
                        continue;
                    }
                    $maps[ $bundle_name ]['files'][$filename] = $path ;
                }
            }
        }
        
        $tree   = array() ;
        foreach($maps as $bundule_name => $resources ) {
            $_bundule   = array(
                'text'  => $bundule_name , 
                'state' => array(
                    'opened'    => false ,
                    'disabled'  => true ,
                ),
                'children'  => array() ,
            );
            if( isset($resources['dirs']) ) foreach($resources['dirs'] as $dir_name => $files ) {
                $_dir = array(
                    'text'  => $dir_name , 
                    'state' => array(
                        'opened'    => false ,
                        'disabled'  => true ,
                    ),
                    'children'  => array() ,
                );
                foreach($files as $file_name => $path ) {
                    $node = array(
                        'text'  => $path , 
                    );
                    if( $path === $default_name ) {
                        $_dir['state']['opened']   = true ;
                        $_bundule['state']['opened']   = true ;
                        $node['state']['selected']   = true ;
                    }
                    $_dir['children'][] = $node ;
                }
                $_bundule['children'][] = $_dir ;
            }
            if( isset($resources['files']) )  foreach($resources['files'] as $dir_name => $default_name ) {
                $node = array(
                        'text'  => $path , 
                    );
                if( $path === $default_name ) {
                    $_bundule['state']['opened']   = true ;
                    $node['state']['selected']   = true ;
                }
                $_bundule['children'] = $node ;
            }
            
            $tree[] = $_bundule ;
        }
        
        return new JsonResponse ( array_values($tree) ) ;
    }
}