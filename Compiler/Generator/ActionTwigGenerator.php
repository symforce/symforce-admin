<?php

namespace Symforce\AdminBundle\Compiler\Generator ;

use Symforce\AdminBundle\Compiler\MetaType\Action\AbstractAction as Action ;

/**
 * Description of ActionGenerator
 *
 * @author loong
 */
class ActionTwigGenerator {
    
    /**
     * @var Action 
     */
    private $action ;
    
    /**
     * @var string 
     */
    private $admin_name ;
    
    /**
     * @var string 
     */
    private $parent_template ;
    
    /**
     * @var string 
     */
    private $template_file ;
    
    /**
     * @var string 
     */
    private $template_name ;
    
    /**
     * @var \Symforce\AdminBundle\Compiler\Generator\PhpWriter 
     */
    private $writer  ;
    
    public function __construct(Action $action) {
        
        $this->action           = $action ;
        $this->admin_name       = $action->admin_object->name ;
        $this->parent_template  = $action->template  ;
        $this->template_file    = $this->admin_name . '.' . $action->name . '.html.twig' ;
        
        /*
        $this->template_name    = 'SymforceAdminBundle:Cache:' . $this->template_file ;
         */
        
        $this->writer   = new \Symforce\AdminBundle\Compiler\Generator\PhpWriter() ;
        
        $this->writer
                ->writeln('{% extends "' . $this->parent_template . '" %}' )
                ->writeln('{% import "' . $action->admin_object->template . '" as admin_macro %}' )
        ;
    }
    
    public function getTemplateName() {
        return $this->template_file ;
    }

    /**
     * @return \Symforce\AdminBundle\Compiler\Generator\PhpWriter
     */
    public function getWriter() {
        return $this->writer ;
    }
    
    public function flush(\Symforce\AdminBundle\Compiler\Generator $gen) {
        
        $template_path   = $gen->getParameter('kernel.root_dir') . '/Resources/views/' . $this->template_file ;
        
        \Dev::write_file($template_path, $this->writer->getContent() ) ;
    }
}
