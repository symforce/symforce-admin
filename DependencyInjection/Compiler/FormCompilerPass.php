<?php

namespace Symforce\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class FormCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {

        /*
        $resources = $container->getParameter('twig.form.resources') ;
        */

        if ( $container->hasDefinition('validator.builder') ) {
            // make sure validator.admin_validator is the first validator

            $validator_factory = $container->getDefinition('validator.validator_factory') ;
            $validators = $validator_factory->getArgument(1) ;
            if( !isset($validators['validator.admin_validator']) ) {
                throw new \Exception ;
            }
            $_validators = array(
                'validator.admin_validator' => null ,
            );
            foreach($validators as $validator_id => $validator_ref ) {
                $_validators[ $validator_id ] = $validator_ref ;
            }
            $validator_factory->replaceArgument(1, $_validators);

        } else {
            // Symfony 2.5~
            $loaderChain = $container->getDefinition('validator.mapping.loader.loader_chain');
            $arguments = $loaderChain->getArguments();
            array_push($arguments[0], new Reference('sf.validator.loader'));
            $loaderChain->setArguments($arguments);
        }
    }
}
