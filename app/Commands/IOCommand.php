<?php

namespace App\Commands;

use CLIFramework\Command;
use App\AST\MSParser;

class IOCommand extends Command
{
    public function brief()
    {
        return "Displays current date and time."; //Short description
    }

    public function execute($input)
    {
        $this->getLogger()->writeln($input);
        $file = fopen($input, "r");
        if ($file == false) {
            echo "Error in opening new file";
            exit();
        }
        $filesize = filesize($input);
        $code = fread($file, $filesize);
        $msParser = new MSParser;
        $msParser->extractFeatures($code);
    }

    public function arguments($args)
    {
        $args->add('input');
    }
}
