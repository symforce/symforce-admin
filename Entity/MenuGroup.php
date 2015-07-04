<?php

namespace App\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\AdminBundle\Compiler\Annotation as Admin ;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_menu_group")
 * @Admin\Entity("app_menu_group", label="菜单组", icon="rss", menu="sys", dashboard=true, class="App\AdminBundle\Admin\MenuGroupAdmin",  groups={
 *      "default": "默认",
 *      "render":"属性",
 *      "child":"子选项"
 * })
 * 
 */
class MenuGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Admin\Table(order=true)
     */
    protected $id ;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Admin\Table(order=true)
     * @Admin\Form()
     * @Admin\ToString()
     */
    public $name ;
    
    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false )
     * @ORM\Column(length=255, unique=false)
     * @Admin\Table()
     * @Admin\Form()
     */
    public $slug ;
    
    /**
     * @ORM\Column(type="string", length=16)
     * @Admin\Form(label="Tag", group="render")
     */
    public $group_tag = 'ul' ;
    
    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Admin\Form(label="Class", group="render")
     */
    public $group_class ;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Admin\Form(label="CSS", group="render", height=4)
     */
    public $group_css ;
    
    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Admin\Form(label="Wapper ", group="child")
     */
    public $child_wapper = 'li' ;
    
    /**
     * @ORM\Column(type="string", length=16)
     * @Admin\Form(label="Tag", group="child")
     */
    public $child_tag = 'a' ;
    
    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Admin\Form(label="Class", group="child")
     */
    public $child_class ;
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(label="active", group="child")
     */
    public $use_active= false ;
    
    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Admin\Form(label="Active Var", group="child", show_on={"use_active":"1"} )
     */
    public $child_active_var ;
    
    /**
     * @ORM\Column(type="string", length=16)
     * @Admin\Form(label="Active Class", group="child", show_on={"use_active":"1"} )
     */
    public $child_active_class = 'actived' ;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Admin\Form(label="CSS", group="child", height=4)
     */
    public $child_css ;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Admin\Form(label="前置内容", group="child", height=2)
     */
    public $child_before_content ;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Admin\Form(label="后置内容", group="child", height=2)
     */
    public $child_after_content ;

    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(label="使用图标", group="child")
     */
    public $use_icon = false ;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Admin\Form(label="默认图标", group="child", show_on={"use_icon":"1"} )
     */
    public $default_icon ;

    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(label="使用图片", group="child")
     */
    public $use_image = false ;
    
    /** 
     * @ORM\OneToOne(targetEntity="App\AdminBundle\Entity\File")
     * @Admin\Form(label="默认图片", type="image", max_size="1m", image_size="96x96", group="child", show_on={"use_image":"1"}, use_crop=false )
     */
    public $default_image ;
    
    /**
     * @ORM\Column(type="integer")
     * @Admin\Form(label="默认图片宽度", group="child", show_on={"use_image":"1"} )
     */
    public $default_image_width = 96 ;
    
    /**
     * @ORM\Column(type="integer")
     * @Admin\Form(label="默认图片高度", group="child", show_on={"use_image":"1"} )
     */
    public $default_image_height = 96 ;
    
    /**
     * @ORM\Column(type="boolean")
     * @Admin\Form(label="多级菜单", group="child")
     */
    public $multi_level= false ;
    
    /**
     * @ORM\Column(type="integer")
     * @Admin\Form(label="菜单层级", group="child", max=5, min=1, show_on={"multi_level":1})
     */
    public $menu_child_deep = 1 ;
    
    /**
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="menu_group", cascade={"remove"} )
     * @ORM\OrderBy({"order_by" = "DESC"})
     * @Admin\Table(label="菜单项目")
     */
    public $menu_list ;
    
    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    public $updated;


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
    
    public function __toString() {
        return $this->name ;
    }
    
}