<?php

namespace App\Commands;

use CLIFramework\Command;
use App\AST\ASTParser;

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
            echo ("Error in opening new file");
            exit();
        }
        $filesize = filesize($input);
        $code = fread($file, $filesize);
        // echo $code;

        $parser = new ASTParser;
        $parser->execute($code);
    }

    public function arguments($args)
    {
        $args->add('input');
    }
}
