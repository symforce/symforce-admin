<?php

namespace Symforce\AdminBundle\Form\Extension;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\InvalidArgumentException;

class DynamicViewTypeExtension extends AbstractTypeExtension
{
        
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
    
    /**
     * @var array
     */
    protected $options = array();

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if( isset( $options['sf_form_type'] ) ) {

            $view->vars['widget_form_group_attr']['sf_form_name'] = $form->getName() ;
            $view->vars['widget_form_group_attr']['sf_form_type'] = $options['sf_form_type'] ;

            /*
            $view->vars['widget_form_group_attr']['sf_form_meta'] = $options['sf_form_meta'] ;
            */

            if( isset($options['sf_form_dynamic']) ) {
                if( !isset($view->vars['widget_form_group_attr']) ) {
                    throw new \Exception("big error, mopa code must changed");
                }
                $view->vars['widget_form_group_attr']['class'] .= ' form-group-hide' ;

                /*
                 * remove because we put all value in hidden input
                 *
                $show_on    = $options['sf_form_dynamic'] ;
                if( !is_array($show_on) ) {
                    $show_on = array( $show_on ) ;
                }
                foreach($show_on as $and_i => $and ) {
                    foreach($and as $when_i => $values ) {
                        if( !is_array($values) ) {
                            $values = explode(',', trim($values) ) ;
                        }
                        foreach($values as $_value_i => $when_value ) {
                            $values[ $_value_i ] = (string) trim($when_value) ;
                        }
                        $show_on[$and_i][$when_i] = $values ;
                    }
                }
                $view->vars['widget_form_group_attr']['sf_form_dynamic'] = json_encode( $show_on ) ;
                */
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'sf_form_type' ,
            'sf_form_meta' ,
            'sf_form_dynamic' ,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}