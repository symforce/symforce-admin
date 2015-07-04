<?php

namespace Symforce\AdminBundle\Twig\Extension;

use CG\Core\ClassUtils;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SymforceAdminTwigExtension extends \Twig_Extension
{
    protected $loader;
    
    /**
     * @var \Symforce\AdminBundle\Compiler\Loader\AdminLoader
     */
    protected $admin_loader;
    
    /**
     * @var ContainerInterface 
     */
    protected $container ;

    public function __construct(\Twig_LoaderInterface $loader)
    {
        $this->loader = $loader;
    }
    
    public function setContainer(ContainerInterface $container ){
        $this->container    = $container ;
        $this->admin_loader = $container->get('sf.admin.loader') ;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'sf_debug' => new \Twig_Function_Method($this, 'sf_debug', array('is_safe' => array('html'))) ,
            'sf_param' => new \Twig_Function_Method($this, 'sf_param', array('is_safe' => array('html'))) ,
            
            'sf_date_format'   => new \Twig_Function_Method($this, 'sf_date_format') ,
            'sf_date_diff'   => new \Twig_Function_Method($this, 'sf_date_diff') ,
            'sf_date_countdown'    => new \Twig_Function_Method($this, 'sf_date_countdown', array('is_safe' => array('html'))) ,
            
            'sf_locale_form'   => new \Twig_Function_Method($this, 'sf_locale_form') ,
            'sf_auth'   => new \Twig_Function_Method($this, 'sf_auth') ,
            'sf_admin_class'   => new \Twig_Function_Method($this, 'sf_class') ,
            'sf_admin'   => new \Twig_Function_Method($this, 'sf_admin') ,
            'sf_admin_path'   => new \Twig_Function_Method($this, 'sf_admin_path') ,
            'sf_path'   => new \Twig_Function_Method($this, 'sf_page_path') ,
            'sf_now'   => new \Twig_Function_Method($this, 'sf_now') ,
            'twig_macro_exists'  => new \Twig_Function_Method($this, 'twig_macro_exists') ,
            'sf_money' => new \Twig_Function_Method($this, 'sf_money') ,
            
            'sf_check_class' => new \Twig_Function_Method($this, 'sf_check_class') ,
            
            'sf_picker_format' => new \Twig_Function_Method($this, 'sf_picker_format') ,
            'sf_string_cut' => new \Twig_Function_Method($this, 'string_cut', array('is_safe' => array('html')) ) ,
            
            'sf_percent' => new \Twig_Function_Method($this, 'sf_percent', array('is_safe' => array('html')) ) ,
            
            'sf_menu'  =>  new \Twig_Function_Method($this, 'sf_menu', array(
                    'needs_environment' => true,
                    'needs_context' => true,
                    'is_safe' => array('html')) ) ,
        );
    }
    
    public function sf_check_class($object, $class) {
        if( !is_object($object) ) {
            throw new \Exception(sprintf("expect class(%s), get(%s)", $class, gettype($object))) ;
        } else if( !($object instanceof $class) ) {
            throw new \Exception(sprintf("expect class(%s), get(%s)", $class,  get_class($object))) ;
        }
    }
    
    public function twig_macro_exists($twig, $macro){
        // $rc = new \ReflectionClass($twig) ; echo $rc->getFileName() , "\n";
        return method_exists($twig, 'get' . $macro ) ;
    }
    
    
    public function sf_money($value, $per = 2 , $currency = 'CNY' ){
        $locale = \Locale::getDefault();
        $format = new \NumberFormatter($locale, \NumberFormatter::CURRENCY) ;
        return $pattern = $format->formatCurrency($value, $currency) ;
    }
    
    public function sf_now(){
        return time() ; 
    }
    
    public function sf_locale_form( \Symfony\Component\HttpFoundation\Request $reqest ) {
        return $this->container->get('sf.locale.listener')->getInlineForm($reqest) ;
    }

    public function sf_auth($admin_name, $action_name = null , $object = null ){
        return $this->admin_loader->auth($admin_name, $action_name, $object ); 
    }
    
    public function sf_admin($admin_name){
        return $this->admin_loader->getAdminByName($admin_name); 
    }
    
    public function sf_class($admin_class){
        return $this->admin_loader->getAdminByClass($admin_class); 
    }
    
    public function sf_admin_path( $admin, $action, $object = null , $options = array() ) {
        $admin  = $this->admin_loader->getAdminByName($admin) ;
        return $admin->path($action, $object, $options ) ;
    }
    
    public function sf_debug( $o , $exit = true ) {
         \Dev::dump($o, 8 ) ;
         if( $exit ) {
             exit ;
         } 
    }
    
    public function sf_page_path( $action, $object = null , $options = array() ) {
        $cache = $this->container->get('sf.page.service') ;
        return $cache->path($action, $object, $options ) ;
    }
    
    public function sf_param( $node )
    {
        if( $this->container->hasParameter($node) ) {
            return $this->container->getParameter($node) ;
        }
        return $node ;
    }
    
    public function sf_date_format($data, $format) {
        if( $data instanceof \DateTime ) {
            return $data->format( $format ) ;
        }
        return $data ;
    }
    
    public function sf_date_countdown(\DateTime $date, $stop_text = null ){
        $options    = array(
            'date'  => $date->format('Y-m-d H:i:s') ,
        ) ;
        if( $stop_text ) {
            $options['pass']    = $stop_text ;
        }
        return '<span class="sf_countdown" data='. var_export(json_encode($options), 1).'></span>';
    }
    
    public function sf_date_diff(\DateTime $date, $now = null , $text = null ) {
        if( null === $now ) {
            $now = time() ;
        }
        $pass = $date->getTimestamp() - $now ;
        if( $pass < 1 ) {
            return $text ;
        }
        $day    = 24 * 3600 ;
        if( $pass > $day ) {
            return ceil( $pass / $day ) . '天' ;
        }
        $hour   = 3600 ;
        if( $pass > $hour ) {
            return ceil( $pass / $hour ) . '小时' ;
        }
        $minute   = 60 ;
        if( $pass > $minute ) {
            return ceil( $pass / $minute ) . '分' ;
        }
        return $pass . '秒' ;
    }
    
    public function sf_picker_format($format, $type ) {
        static $cache   = array() ;
        if( isset($cache[$type][$format]) ) {
            return $cache[$type][$format] ;
        }
        /**
         * @TODO fix format and add apc cache
         */
        static $map = array(
            'date'   => array(
                'Y' => 'yyyy' ,
                'y' => 'yy' ,
                'm' => 'mm' ,
                'n' => 'm' ,
                'd' => 'dd' ,
                'j' => 'd' ,
                'H' =>  'HH' , 
                'G' =>  'H' , 
                'i' => 'II' ,
                's' => 'SS' ,
            ) ,
            'datetime'  => array(
                'Y' => 'yyyy' ,
                'y' => 'yy' ,
                'm' => 'mm' ,
                'n' => 'm' ,
                'd' => 'dd' ,
                'j' => 'd' ,
                'H' =>  'hh' , 
                'G' =>  'h' , 
                'i' => 'ii' ,
                's' => 'ss' ,
            ) ,
        );
        
        if( !isset($map[$type]) ) {
            throw new \Exception(sprintf("unknow type(%s), accept(%s)", $type, join(",", array_keys($map) ) ));
        }
        
        $_format = preg_replace_callback('/\w/', function($m) use ( & $map, $type ){
            $_key   = $m[0] ;
            if( isset( $map[$type][ $_key ]) ) {
                return $map[$type][ $_key ] ;
            }
            return $_key ;
        }, $format); 
        
        $cache[$type][$format] = $_format ;
        return $_format ;
    }
    
    public function string_cut( $content, $limit = 29 ) {
        $code   = strip_tags($content);
        if( mb_strlen($code, 'UTF-8') > $limit ) {
            $code = mb_substr( $code , 0, $limit, 'UTF-8') ;
            $code = $code  . '...' ;
        }
        return  $code ; 
    }
    
    public function sf_percent($number){
        return $number * 100 / 100 ;
    }

    public function sf_menu( \Twig_Environment $env, array & $context, $name, array $args = array() ) {
        $sf_menu =  $this->container->get('sf.page.menu') ;
        return $sf_menu->render($env, $context, $name, $args ) ;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sf.admin';
    }
}
