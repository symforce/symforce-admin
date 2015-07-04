<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\AdminBundle\Command;

use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Util\VarUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dumps assets to the filesystem.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class FileCommand extends ContainerAwareCommand
{
    /**
     * @var \App\AdminBundle\Compiler\Loader\AdminLoader
     */
    private $loader ;
    
    /**
     * @var \App\AdminBundle\Compiler\Cache\AdminCache
     */
    private $admin ;
    
    private $force ;
    private $dev ;
    
    protected function configure()
    {
        $this
            ->setName('app:files:clear')
            ->setDescription('Clear unused file cache')
            ->addOption('force', null, InputOption::VALUE_NONE, 'run the sql')
            ->addOption('dev', null, InputOption::VALUE_NONE, 'debug')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->loader   = $this->getContainer()->get('app.admin.loader') ;
        $this->force    = $input->getOption('force') ;
        $this->dev      = $input->getOption('dev') ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $em  = $this->getContainer()->get('doctrine')->getManager() ;
        
        $dql    = sprintf("SELECT a FROM App\AdminBundle\Entity\File a WHERE a.session_id IS NOT NUll OR a.entity_id=0 ORDER BY a.updated ASC"); 
         
        $query   = $em->createQuery($dql);
        $query->setMaxResults( 0x7ff ) ;
        
        $total_size = 0 ;
        $now    = new \DateTime('now') ;
        foreach($query->getResult() as $file) {
            $date   = $file->getUpdated() ;
            if( !$date ) {
                $date   = $file->getCreated() ;
            }
            if( !$date ) {
                continue ;
            }
            $passed_time    = $now->getTimestamp() - $date->getTimestamp() ;
            if( $passed_time < 3600 * 12 ) {
                continue ;
            }
            $output->writeln( sprintf("%d   => %s , %s", $file->getId(), $file->getName(), $date->format('y/m/d H:i:s') )) ;
            $em->remove( $file ) ;
            $total_size += $file->getSize() ;
        }
        
        $output->writeln( sprintf("total_size = %sMB ", round($total_size/1024/1024, 3 ) )) ;
        if( $this->force ) {
            $em->flush() ;
        }
    }

}