<?php

namespace Symforce\AdminBundle\DependencyInjection\Composer;

class Script
{

    public static function Install(\Composer\Script\Event $event) {

        exec('./app/console assets:install --symlink --relative');
        exec('./app/console symforce:admin:setup');
        exec('./app/console symforce:admin:dump --force');
        
        return true;
    }
    
}