<?php

namespace Symforce\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symforce\AdminBundle\Compiler\Annotation as Admin ;


/**
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="app_pages")
 * @Admin\Entity("app_page", label="Page", icon="archive", position=3, menu="admin_group", dashboard=true , groups={
 *      "default": "默认" ,
 *      "content":"内容",
 *      "route":"路由" ,
 *      "seo":"搜索引擎优化",
 *      "admin":"管理"
 * })
 * 
 * @Gedmo\Tree(type="nested") 
 */
class Page 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Admin\Table()
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     * @Admin\Form( required=true )
     * @Admin\Table
     */
    public $title;
    
    /**
     * @Gedmo\Slug(fields={"title"}, updatable=false )
     * @ORM\Column(length=255, unique=true)
     * @Admin\Form(group="seo")
     * @Admin\Table()
     */
    protected $slug;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(type="textarea", height=2, group="seo")
     */
    public $meta_keywords ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(type="textarea", height=3, group="seo")
     */
    public $meta_description ;

    /**
     * @ORM\OneToOne(targetEntity="Symforce\AdminBundle\Entity\File")
     * @Admin\Form(group="content", type="image", max_size="1m", image_size="1920x470", use_crop=false )
     */
    public $image;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Admin\Form(group="content", type="html")
     * @Gedmo\Translatable
     */
    public $content ;
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(type="bool", auth=true, group="content")
     */
    public $content_top_enabled = 0 ;
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(type="bool", group="content", show_on={"content_top_enabled":1})
     */
    public $content_top_inherit = 1 ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(type="choice", group="content", show_on={"content_top_inherit":0}, choices={"template":"模板", "html":"内容"} )
     */
    public $content_top_type ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(type="template", group="content", show_on={ "content_top_type":"template"})
     */
    public $content_top_template ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(group="content", show_on={"content_top_type":"html"})
     */
    public $content_top_class ;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Admin\Form(type="html", group="content", show_on={"content_top_type":"html"})
     */
    public $content_top_html ;
    
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(type="bool", auth=true, group="content")
     */
    public $content_menu_enabled = 0 ;
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(type="bool", group="content", show_on={"content_menu_enabled":1})
     */
    public $content_menu_inherit = 1 ;
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(type="bool", group="content", show_on={"content_menu_inherit":0})
     */
    public $content_menu_left = 1 ;
    
    /**
     * @ORM\Column(type="integer")
     * @Admin\Form(group="content", show_on={ "content_menu_inherit":0})
     */
    public $content_menu_width = 6 ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(group="content", show_on={"content_menu_inherit":0})
     */
    public $content_menu_name ;
    
    
    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    public $created ;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    public $updated;

    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(type="bool", auth=true, group="route")
     * @Admin\Table
     */
    public $root_enabled = 0 ;

    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Table()
     * @Admin\Form( type="route", auth=true, group="route", show_on={"root_enabled":1})
     */
    public $route_name ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(type="json", auth=true, group="route", show_on={"root_enabled":1})
     */
    public $route_args ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(type="template", auth=true, group="route", show_on={"root_enabled":1})
     */
    public $route_template ;
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(group="route",  auth=true, show_on={ {"root_enabled":1, "tree_leaf":"0"}, {"root_enabled":1, "admin_is_root":"1"} } )
     */
    public $use_child_template = false ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true )
     * @Admin\Form(type="template",  auth=true, group="route", show_on={"use_child_template": 1 } )
     */
    public $route_child_template ;
    
    // ========= admin ===========
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(type="bool", auth=true, group="admin")
     * @Admin\Table
     */
    public $admin_enabled = 0 ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(auth=true, group="admin", show_on={"admin_enabled":1})
     */
    public $admin_class ;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Admin\Form(auth=true, group="admin", show_on={"admin_enabled":1})
     */
    public $admin_page_property ;
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(auth=true, group="admin", show_on={"admin_enabled":1})
     */
    public $admin_is_root = false ;
    
    /**
     * @ORM\Column(type="integer")
     * @Admin\Form(auth=true, group="admin", show_on={"admin_enabled":1})
     */
    public $admin_entity_id = 0 ;
    
    // ======= other ========
    
    /**
     * @ORM\Column(type="integer")
     * @Admin\Form(position=1, group="seo", auth=true)
     * @Admin\Table()
     */
    public $order_by = 0 ;
    
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
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="children", cascade={"persist"} )
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(auth=true , show_on={"admin_enabled":0} )
     * @Admin\Table
     * @Admin\TreeLeaf
     */
    public $tree_leaf = 0 ;
    
    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="parent")
     * @ORM\OrderBy({"tree_left_node" = "ASC"})
     */
    public $children ;
    

    public function __construct()
    {
        
    }
    
    public function getId()
    {
        return $this->id ;
    }

    public function setSlug( $slug )
    {
        $this->slug = $slug ;
    }

    public function getSlug()
    {
        if( '-1' === $this->slug ) {
            return '-' ;
        }
        return $this->slug ;
    }

    public function setParent(Page $parent = null)
    {
        $this->parent = $parent;
    }
    
    /**
     * @return Page
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    public function getPageTemplate(){
        if( $this->route_template ) {
            return $this->route_template ;
        }
        if( $this->parent ) {
            return $this->parent->getChildPageTemplate() ;
        }
    }
    
    protected function getChildPageTemplate() {
        if( null !== $this->route_child_template ) {
            return $this->route_child_template ;
        }
        if( $this->parent ) {
            return $this->parent->getChildPageTemplate() ;
        }
    }
    
    public function getPageUrl() {
        $url    = '/' . $this->slug ;
        if( $this->parent ) {
            $url = $this->parent->getPageUrl() . $url ;
        }
        return $url ;
    }
    
    public function getContentMenuEnabled(){
        return $this->content_menu_enabled ;
    }
    
    public function getPageContentMenuLeft(){
        if( $this->content_menu_inherit ) {
            if( $this->parent ) {
                return $this->parent->getPageContentMenuLeft() ;
            }
        }
        return $this->content_menu_left;
    }
    
    
    public function getPageContentMenuWidth(){
        if( $this->content_menu_inherit ) {
            if( $this->parent ) {
                return $this->parent->getPageContentMenuWidth() ;
            }
        }
        return $this->content_menu_width ;
    }
    
    public function getPageContentMenuName(){
        if( $this->content_menu_inherit ) {
            if( $this->parent ) {
                return $this->parent->getPageContentMenuName() ;
            }
        }
        return $this->content_menu_name ;
    }
    
    public function getPageContentTopType(){
        if( $this->content_top_inherit ) {
            if( $this->parent ) {
                return $this->parent->getPageContentTopType() ;
            }
        }
        return $this->content_top_type ;
    }

    public function getPageContentTopClass(){
        if( $this->content_top_inherit ) {
            if( $this->parent ) {
                return $this->parent->getPageContentTopClass() ;
            }
        }
        return $this->content_top_class ;
    }
    
    
    public function getPageContentTopTemplate(){
        if( $this->content_top_inherit ) {
            if( $this->parent ) {
                return $this->parent->getPageContentTopTemplate() ;
            }
        }
        return $this->content_top_template ;
    }
    
    public function getPageContentTopHtml(){
        if( $this->content_top_inherit ) {
            if( $this->parent ) {
                return $this->parent->getPageContentTopHtml() ;
            }
        }
        return $this->content_top_html ;
    }
    
    public function __toString() {
        return $this->title ;
    }
    
    
}