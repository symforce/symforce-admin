<?php

namespace Symforce\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symforce\AdminBundle\Form\DataTransformer\TreeTransformer ;

class TreeType extends AbstractType {
    
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
        $transformer    = new TreeTransformer( $this->container->get('sf.admin.loader'), $options['target_class'] ) ;
        $builder->addViewTransformer( $transformer ) ; 
    }
    
    public function getName(){
        return 'sf_tree' ;
    }
    
    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $loader = $this->container->get('sf.admin.loader') ;
        $view->vars['admin']   = $loader->getAdminByClass( $options['admin_class'] ) ;
        $view->vars['target_admin']   =  $loader->getAdminByClass( $options['target_class'] ) ;
        if( $options['copy_property'] && isset($view->vars['form']->parent[ $options['copy_property'] ]) ) {
            $view->vars['copy_property']   =  array( $options['copy_property'] , $view->vars['form']->parent[ $options['copy_property'] ]->vars['id'] ) ;
        } else {
            $view->vars['copy_property']   =  array( $options['copy_property'] , null ) ;
        }
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
       
        $resolver->setDefaults( array(
            'compound'       => false ,
            'copy_property' => false ,
        ));
        
        $resolver->setRequired(array(
             'admin_class' ,
             'admin_property' ,
             'target_class' 
        ));
    }
}
