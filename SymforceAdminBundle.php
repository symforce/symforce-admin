<?php

namespace Symforce\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\Console\Application;

use Symforce\AdminBundle\DependencyInjection\Compiler ;

class SymforceAdminBundle extends Bundle
{
    
    /**
     * {@inheritDoc}
     */
    public function registerCommands(Application $application)
    {
        $application->add(new Command\SetupCommand());
        $application->add(new Command\DumpCommand());
        $application->add(new Command\WorkflowCommand());
        $application->add(new Command\FileCommand());
    }


    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /*
            since Symfony 2.7, not need this
            \Doctrine\Common\Annotations\AnnotationRegistry::registerFile( __DIR__ . '/Compiler/Annotation/All.php') ;
        */
        
        $container->addCompilerPass(new Compiler\ChainRouterPass());
        $container->addCompilerPass(new Compiler\AdminLoaderPass());
        $container->addCompilerPass(new Compiler\ValidatorCompilerPass());
        $container->addCompilerPass(new Compiler\FormCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING );
    }
    
}
