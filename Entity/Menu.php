<?php

namespace Symforce\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symforce\AdminBundle\Compiler\Annotation as Admin ;

/**
 * @ORM\Entity
 * @ORM\Table(name="sf_menu")
 * @Admin\Entity("menu", label="菜单", icon="menu")
 * 
 * @Gedmo\Tree(type="nested") 
 * 
 */
class Menu
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Admin\Table(order=true)
     */
    protected $id ;
    
    /**
     * @var MenuGroup
     * @ORM\ManyToOne(targetEntity="MenuGroup", inversedBy="menu_list", cascade={"persist"} )
     * @Admin\Table() 
     */
    public $menu_group ;
    
    /**
     * @ORM\Column(type="array")
     * @Admin\Form(type="choice", choices={"page":"页面", "static":"静态", "route":"路由", "page_route":"页面路由" } )
     * @Admin\Table()
     */
    public $type ;

    /**
     * @Admin\Table(label="名")
     */
    protected $menu_title ;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(required=true, show_on={"type":"static,route"} )
     */
    public $title ;

    /**
     * @ORM\ManyToOne(targetEntity="Symforce\AdminBundle\Entity\Page", cascade={"detach" } )
     * @Admin\Form(label="页面", required=true, type="tree", show_on={"type":"page,page_route"} )
     * @Admin\Table()
     */
    public $page ;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(show_on={"type":"page_route"} )
     */
    public $page_menu_title ;

    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Table()
     * @Admin\Form( type="route", required=true, show_on={"type":"route,page_route"} )
     */
    public $route_name ;

    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(type="json", show_on={"type":"route"})
     */
    public $route_args ;

    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(show_on={"type":"page_route"})
     */
    public $page_route_arg_name ;

    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(type="text", required=true, show_on={"type":"static"})
     */
    public $static_url ;

    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form()
     */
    public $disabled = false ;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Admin\Form(label="图标", group="css", parent_on={"menu_group":{"use_icon":"1"}} )
     */
    public $icon ;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Admin\Form(label="子图标", group="css", parent_on={"menu_group":{"use_icon":"1"}} , show_on={"has_child":0} )
     */
    public $child_icon;

    /**
     * @ORM\OneToOne(targetEntity="Symforce\AdminBundle\Entity\File")
     * @Admin\Form(label="图片", type="image", max_size="1m", image_size="120x130", small_size="12x12", group="css", parent_on={"menu_group":{"use_image":"1"}} )
     */
    public $image ;

    /**
     * @ORM\OneToOne(targetEntity="Symforce\AdminBundle\Entity\File")
     * @Admin\Form(label="子图片", type="image", max_size="1m", image_size="120x130", small_size="12x12", group="css", parent_on={"menu_group":{"use_image":"1"}} , show_on={"has_child":0} )
     */
    public $child_image ;

    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(label="样式控制", group="css" )
     */
    public $enable_css = false ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(group="css", show_on={"enable_css":1})
     */
    public $menu_id ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(group="css", show_on={"enable_css":1})
     */
    public $link_target ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(group="css", show_on={"enable_css":1})
     */
    public $menu_class ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(type="textarea", height=4, group="css", show_on={"enable_css":1})
     */
    public $menu_css ;

    /**
     * @ORM\Column(type="integer")
     * @Admin\Form(group="child", max=99, min=-99)
     * @Admin\Table(order=true)
     */
    public $order_by = 0 ;
    
    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created ;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated ;
    
    // ========= tree ========
    
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $tree_left_node;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $tree_level ;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $tree_right_node;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $tree_root_node;
    
    /**
     * @Gedmo\TreePath
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $tree_path ;
    
    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children", cascade={"persist"} )
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(label="叶子节点", group="child", parent_on={"menu_group":{"multi_level":"1"}} )
     * @Admin\Table
     * @Admin\TreeLeaf
     */
    public $has_child = 1 ;
    
    /**
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent")
     * @ORM\OrderBy({"tree_left_node" = "ASC"})
     */
    public $children ;
    

    public function getId()
    {
        return $this->id ;
    }
    
    public function getCreated()
    {
        return $this->created;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return Menu
     */
    public function getMenuTitle()
    {
        return $this->__toString() ;
    }

    /**
     * @return Menu
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getIconClassName() {
        if( ! $this->menu_group->use_icon ) {
            return null ;
        }
        if( $this->icon ) {
            return $this->icon ;
        }
        if( $this->parent ) {
            return $this->parent->getIconClassName() ;
        }
        return  $this->menu_group->default_icon ;
    }
    
    public function __toString() {
        if( 'page' === $this->type ) {
            return $this->page->__toString() ;
        }
        if( 'page_route' === $this->type ) {
            if ( $this->page_menu_title ) {
                return $this->page_menu_title ;
            }
            return $this->page->__toString() ;
        }
        return $this->title ;
    }
}