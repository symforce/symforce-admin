<?php

namespace Symforce\AdminBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType ;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;


use Symfony\Component\OptionsResolver\OptionsResolverInterface;
//use Symforce\AdminBundle\Form\DataTransformer\HtmlTransformer ;

use Symforce\AdminBundle\Entity\File ;

class HtmlType extends TextareaType {
      
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
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        //$builder->addViewTransformer( new HtmlTransformer() ) ;
        
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use ($options){
            // respond to the event, modify data, or form elements
            $data = $event->getData();
            $object = $event->getForm()->getParent()->getData() ;
            if( !$object ) {
                return ;
            }
            
            $admin  = $this->container->get('sf.admin.loader')->getAdminByClass( $options['sf_admin_class'] ) ;
            $em     = $admin->getManager() ;
            $className  = $admin->getClassName() ;
            
            $property_name  = $options['sf_admin_property'] ;
            
            $oldValue = $admin->getReflectionProperty($property_name)->getValue($object) ;
            
            if( $data ) {
                $pattern = \Symforce\AdminBundle\Entity\File::getRichTextFilesPattern($this->container->getParameter('sf.web_assets_dir') ) ;
                preg_match_all( $pattern, $data, $ms, PREG_SET_ORDER );
                
                $repo       = $em->getRepository('Symforce\AdminBundle\Entity\File') ;
                $object_id  = $admin->getId( $object ) ;
                
                $is_debug = $this->container->getParameter('kernel.debug') ;
                
                if( $ms ) foreach($ms as $ma) {
                                $file   = $repo->loadByUUID( $ma[1] ) ;
                                $file_changed   = false ;
                                if(  $file  && $file->getIsHtmlFile() ) {
                                    if( $is_debug ) {
                                        if( $className !== $file->getClassName() ) {
                                            $file->setClassName( $className ) ;
                                            $file_changed   = true ;
                                        }
                                        if( $property_name !== $file->getPropertyName() ) {
                                            $file->setPropertyName( $property_name ) ;
                                            $file_changed   = true ;
                                        }
                                    } else {
                                        if(  $className !== $file->getClassName() || $property_name !== $file->getPropertyName() ) {
                                            continue ;
                                        }
                                    }
                                    
                                    /*
                                    $this->container->get('sf.admin.loader')->getAdminByClass($object)->addEvent('flushed', function($object, $admin) use($file, $em ){
                                        \Dev::dump($file);
                                        $em->refresh($file);
                                        \Dev::dump($file);
                                        exit;
                                    });
                                     */
                                    
                                    if( $object_id && $object_id !== $file->getEntityId()  ) {
                                        $file->setEntityId( $object_id ) ;
                                        $file_changed   = true ;
                                    }
                                    if( $file->getSessionId() ) {
                                        $file->setSessionId( null ) ;
                                        $file_changed   = true ;
                                    }
                                    
                                    
                                    
                                    if( $file_changed ) {
                                        $em->persist($file) ;
                                    }
                                }
                        }
                
            } else {
                // check nullable 
                $event->setData('') ;
            }
        });
    }
    
    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['sf_admin_name']    = $options['sf_admin_name'] ;
        $view->vars['sf_admin_id']     = $options['sf_admin_id'] ;
        $view->vars['sf_admin_class']    = $options['sf_admin_class'] ;
        $view->vars['sf_admin_property']    = $options['sf_admin_property'] ;
        
        $view->vars['html_options']    = $options['html_options'] ;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
         
        $resolver->setRequired(array(
             'sf_admin_class' ,
             'sf_admin_property' ,
             'sf_admin_name' , 
             'sf_admin_id' , 
            
             'html_options' ,
        ));

    }
    
    public function getName(){
        return 'sf_html' ;
    }
    
    public function getParent()
    {
        return 'textarea';
    }
}
