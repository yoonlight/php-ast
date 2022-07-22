<?php

namespace App\Commands;

use CLIFramework\Command;
use App\AST\MSParser;
use Exception;

class IOCommand extends Command
{
    public function brief()
    {
        return "Displays current date and time."; //Short description
    }

    public function execute($input)
    {
        $file = fopen($input, "r");
        if ($file == false) {
            throw new Exception("Error in opening new file");
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
