<?php

namespace App;

use CLIFramework\Application;

class Console extends Application
{
    const NAME = 'PHP AST Parser CLI';
    /* register your command here */
    public function init()
    {
        parent::init(); // Standard commands
        $this->command('parse', \App\Commands\IOCommand::class);
    }
}
