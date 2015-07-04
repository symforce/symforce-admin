<?php

namespace Symforce\AdminBundle\DependencyInjection\Composer;

class Script
{

    public static function Install(\Composer\Script\Event $event) {

        exec('./app/console assets:install --symlink --relative');
        exec('./app/console sf:admin:setup');
        exec('./app/console sf:admin:dump --force');
        
        return true;
    }
    
}