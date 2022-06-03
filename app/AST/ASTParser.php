<?php

namespace App\AST;

use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;

class ASTParser
{
    public function execute($code)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($code);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }

        $dumper = new NodeDumper;
        echo $dumper->dump($ast) . "\n";
    }
}
