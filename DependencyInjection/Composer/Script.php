<?php

namespace App\AdminBundle\DependencyInjection\Composer;

use Composer\Script\Event;

class Script
{

    public static function Install(Event $event) {

        exec('./app/console assets:install --symlink --relative');
        exec('./app/console app:admin:setup');
        exec('./app/console app:admin:dump --force');
        
        return true;
    }
    
}