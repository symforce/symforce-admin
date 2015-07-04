<?php

namespace Symforce\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ValidatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        return ;
        
        $taggedServices = $container->findTaggedServiceIds(
            'symforce.admingenerator.validator'
        );

        $taggedServicesDoctrine = $container->findTaggedServiceIds(
            'symforce.admingenerator.doctrine'
        );

        $taggedServicesDoctrineOdm = $container->findTaggedServiceIds(
            'symforce.admingenerator.doctrine_odm'
        );

        if ($container->hasDefinition('symforce.admingenerator.generator.doctrine')) {
            $this->addValidators($taggedServicesDoctrine, $container->getDefinition('admingenerator.generator.doctrine'));
            $this->addValidators($taggedServices, $container->getDefinition('admingenerator.generator.doctrine'));
        }

        if ($container->hasDefinition('admingenerator.generator.doctrine_odm')) {
            $this->addValidators($taggedServicesDoctrineOdm, $container->getDefinition('admingenerator.generator.doctrine_odm'));
            $this->addValidators($taggedServices, $container->getDefinition('admingenerator.generator.doctrine_odm'));
        }
    }

    protected function addValidators(array $taggedServices, Definition $definition)
    {
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addValidator',
                array(new Reference($id))
            );
        }
    }
}
