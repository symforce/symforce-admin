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

class TemplateType extends AbstractType {
    
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
    
    public function getName(){
        return 'sf_template' ;
    }
    
    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
       
        $resolver->setDefaults( array(
            'compound'       => false ,
        ));
        
        $resolver->setRequired(array(
            
        ));
    }
}
