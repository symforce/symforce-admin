<?php

namespace Symforce\AdminBundle\Service;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Description of Menu
 *
 * @author loong
 */
class MenuService {

    const UNKNOW_TEMPLATE   = 'AppAdminBundle:Menu:unknow.html.twig' ;

    private $debug  = false ;
    
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container A ContainerInterface instance
     *
     */
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * @return  \AppAdminCache\AppMenuGroup\AdminAppMenuGroup
     */
    public function getGroupAdmin(){
        return $this->container->get('app.admin.loader')->getAdminByName('app_menu_group') ;
    }

    /**
     * @return  \Symforce\AdminBundle\Admin\MenuAdmin
     */
    public function getMenuAdmin(){
        return $this->container->get('app.admin.loader')->getAdminByName('app_menu') ;
    }

    /**
     * @return  \Symforce\AdminBundle\Admin\PageAdmin
     */
    public function getPageAdmin(){
        return $this->container->get('app.admin.loader')->getAdminByName('app_page') ;
    }
    
    public function render(\Twig_Environment $twig, array & $context, $name, array $args = array() ) {

        $group = $this->getGroupAdmin()->getRepository()->findOneBy( array(
            'slug'  => $name ,
        ));
        
        $args['app_group_name'] = $name ;

        if( !$group ) {
            $template   = self::UNKNOW_TEMPLATE ;
        } else {
            $updated = $group->updated ;
            $template  = 'app_menu_cached_' . $name . '.html.twig' ;
            $path   = $this->container->getParameter('kernel.root_dir')  . '/Resources/views/' . $template ;
            if( $this->debug || !file_exists($path) || filemtime($path) < $updated->getTimestamp() ) {
                $this->compileGroup( $group, $path) ;
            }

            if( isset($context['page']) ) {
                $args['page']   = $context['page'] ;
            } else {
                $args['page']   = null ;
            }

            if( $group->child_active_var  ){
                if(  isset( $context[ $group->child_active_var ]) ) {
                    $args['active_var']   = $context[ $group->child_active_var ] ;
                } else {
                    $args['active_var']   = null ;
                }
            }
        }

        $args['menu_service']   = $this ;

        return $twig->render( $template , $args ) ;
    }
    
    protected function compileGroup(\Symforce\AdminBundle\Entity\MenuGroup $group, $path ) {
        $writer = new \Symforce\AdminBundle\Compiler\Generator\PhpWriter() ;

        $id     = 'app_menu_group_' . $group->slug ;
        
        $writer->writeln( sprintf('<!-- app menu group( %s ) -->', $group->name ));
        if( $group->group_css ) {
            $group_css  = $group->group_css  ;
            $child_css  = $group->child_css  ;
            $writer->writeln( sprintf('<style type="text/css">%s</style>', $group_css ));
        }
        $writer->writeln( sprintf('<div id="%s" class="app_menu_box %s">', $id, $group->group_class ));
        $writer->writeln( sprintf('<%s>', $group->group_tag ));

        foreach($group->menu_list as $menu ) {
            if( $menu->getParent() ) {
                continue ;
            }
            if( $menu->disabled ) {
                continue ;
            }
            $this->compileMenu($menu, $writer ) ;
        }

        $writer->writeln( sprintf('</%s>', $group->group_tag ));
        $writer->writeln( sprintf('</div>'));
        $writer->writeln( sprintf('<!-- /app menu group( %s ) -->', $group->name ));

        \Dev::write_file($path,  $writer->getContent() ) ;
    }

    protected function compileMenu(\Symforce\AdminBundle\Entity\Menu $menu, \Symforce\AdminBundle\Compiler\Generator\PhpWriter $writer, $deep = 0 ) {

        $li_tag = $menu->menu_group->child_wapper ;
        $text_tag  = $menu->menu_group->child_tag ;

        $class  = sprintf('app_menu_item app_menu_deep%d ', $deep ) . $menu->menu_group->child_class ;

        if( 'a' === $text_tag ) {
            $text_tag  = null ;
        }
        $icon   = $menu->getIconClassName() ;

        $page   = null ;
        $text   = null ;
        $url    = '#' ;
        if( 'static' === $menu->type ) {
            $url   = $menu->static_url ;
        } else if( 'route' === $menu->type ) {
            $route_args = json_decode( $menu->route_args ) ;
            if( !is_array($route_args) ) {
                $route_args = array() ;
            }
            $url   = '{{ ' . sprintf('path("%s",%s)', $menu->route_name, json_encode($route_args) ) . ' }}' ;
        } else if( 'page' === $menu->type ) {
            $url   = '{{ ' . sprintf('menu_service.getPageUrl(%d)', $menu->page->getId() ) . ' }}' ;
            $text  = '{{ ' . sprintf('menu_service.getPageText(%d)', $menu->page->getId() ) . ' }}' ;
        } else if( 'page_route' === $menu->type ) {
            $arg_name = $menu->page_route_arg_name ;
            if( !$arg_name ) {
                $arg_name   = 'slug' ;
            }
            $url   = '{{ ' . sprintf('path("%s",{"%s": menu_service.getPageSlug(%d) })', $menu->route_name, $arg_name, $menu->page->getId() ) . ' }}' ;

            if ( !$menu->page_menu_title ) {
                $text  = '{{ ' . sprintf('menu_service.getPageText(%d)', $menu->page->getId() ) . ' }}' ;
            }
        }

        if( !$text ) {
            $text   = '{{ ' . sprintf('menu_service.getMenuItemText(%d)', $menu->getId() ) . ' }}' ;
        }

        if( $menu->menu_group->child_active_class  ) {
            if( $menu->menu_group->child_active_var ) {
                throw new \Exception('not implement yet') ;
            } else {
                $class  .=  '{{ ' . sprintf('menu_service.getMenuItemActiveClass(%d, page)', $menu->getId() ) . ' }}' ;
            }
        }

        if( $li_tag ) {
            $writer->write( sprintf('<%s class="%s">', $li_tag, $class));
            $writer->write( sprintf('<a href="%s">', $url));
        } else {
            $writer->write( sprintf('<a href="%s" class="%s">', $url, $class));
        }
        
        if( $text_tag ) {
            $writer->write( sprintf('<%s>', $text_tag ));
        }

        if( $icon ){
            $writer->write( sprintf('<i class="fa fa-%s"></i>', $icon ));
        }

        $writer->write( $text ) ;
        
        if( $text_tag ) {
            $writer->write( sprintf('</%s>', $text_tag ));
        }
        
        if( $li_tag ) {
            $writer->write( sprintf('</a>'));
            $this->compileMenuChildren($menu, $writer, $deep);
            $writer->writeln( sprintf('</%s>', $li_tag));
        } else {
            $this->compileMenuChildren($menu, $writer, $deep);
            $writer->writeln( sprintf('</a>'));
        }

    }


    protected function compileMenuChildren(\Symforce\AdminBundle\Entity\Menu $menu, \Symforce\AdminBundle\Compiler\Generator\PhpWriter $writer, $_deep ) {
        if( !$menu->menu_group->multi_level ) {
            return ;
        }
        $deep  = $_deep + 1 ;
        if( $deep > $menu->menu_group->menu_child_deep ) {
            return ;
        }
        $tag    = $menu->menu_group->group_tag ;

        $has_children   = false ;

        foreach($menu->children as $child ) {
            if( $child->disabled ) {
                continue ;
            }
            if( !$has_children ) {
                $writer->writeln( sprintf('<%s class="app_menu_items app_menu_items_deep%d">', $tag , $_deep ));
                $has_children   = true ;
            }
            $this->compileMenu($child, $writer, $deep );
        }

        if( $has_children ) {
            $writer->writeln( sprintf('</%s>', $tag ));
        }
    }

    /**
     * @param $menu_id
     * @return  \Symforce\AdminBundle\Entity\Menu
     */
    protected function getMenuById( $menu_id ) {
        return $this->getMenuAdmin()->getObjectById($menu_id) ;
    }

    public function getMenuItemActiveClass($menu_id, \Symforce\AdminBundle\Entity\Page $page = null ) {
        $menu   = $this->getMenuById($menu_id) ;
        if( !$menu ) {
            return ;
        }
        if( 'page' === $menu->type || 'page_route' === $menu->type ) {
            if( $menu->page && $menu->page->getId() == $page->getId() ) {
                return 'menu_item_actived' ;
            }
        } else if( 'route' === $menu->type ) {

        } else { // static

        }
    }

    public function getMenuItemText($menu_id) {

    }

    public function getPageSlug($page_id){
        $page = $this->getPageAdmin()->getObjectById($page_id) ;
        if( !$page ) {
            return 'page_' . $page_id ;
        }
        return $page->getSlug() ;
    }

    public function getPageText($page_id){
        $page = $this->getPageAdmin()->getObjectById($page_id) ;
        return $page->__toString() ;
    }

    public function getPageUrl($page_id){
        $page = $this->getPageAdmin()->getObjectById($page_id) ;
        return $page->getPageUrl() ;
    }
}
