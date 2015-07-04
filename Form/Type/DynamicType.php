<?php

namespace Symforce\AdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\HiddenType ;

class DynamicType extends HiddenType {
    
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container A ContainerInterface instance
     *
     */
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function getName(){
        return 'sf_dynamic' ;
    }
    
    public function getParent()
    {
        return 'hidden' ;
    }
    
    
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $validator = new \Symforce\AdminBundle\Form\Validator\DynamicValidator (
            $this->container 
        );
        
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($validator, 'preSubmit'));
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options) {
        
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        parent::setDefaultOptions($resolver);
        
        $resolver->setRequired(array(
             
        ));
        
        $resolver->setDefaults(array(
            'mapped'   => false ,
        ));
    }
}