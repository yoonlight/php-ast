<?php

namespace App\AST;

use App\Common\MethodContent;
use App\FeatureEntities\ProgramFeatures;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node;
use App\Visitors\FunctionVisitor;

class FeatureExtractor
{
    public $lparen = "(";
    public $rparen = ")";
    public $upSymbol = "^";
    public $downSymbol = "_";
    private $emptyString = "";

    public function extractFeatures($code)
    {
        $arr = [];
        $astNode = $this->parseFile($code);
        foreach ($astNode->getDescendantNodes() as $descendant) {
            $nodeType = $descendant->getNodeKindName();
            $childLength = count(iterator_to_array($descendant->getChildNodes(), false));
            if ($childLength === 0 && $nodeType != "exprInlineHtml" && $nodeType != "InlineHtml") {
                $arr[] = $descendant;
            }
        }
        $functionVisitor = new FunctionVisitor();
        $functionVisitor->visit($astNode);
        $methods = $functionVisitor->getMethodContents();
        $programs = [];
        $programs = $this->generatePathFeatures($methods);
        echo $this->featuresToString($programs);
    }

    public function featuresToString($features)
    {
        $methodsOutputs = [];

        foreach ($features as $singleMethodFeatures) {
            $methodsOutputs[] = $singleMethodFeatures->toString();
        }
        return implode("\n", $methodsOutputs);
    }

    public function parseFile($code)
    {
        $parser = new Parser();
        return $parser->parseSourceFile($code);
    }

    /**
     * @return ProgramFeatures[]
     */
    public function generatePathFeatures(array $methods)
    {
        $methodsFeatures = [];
        foreach ($methods as $content) {
            $singleMethodFeatures = $this->generatePathFeaturesForFunction($content);
            $methodsFeatures[] = $singleMethodFeatures;
        }
        return $methodsFeatures;
    }

    public function generatePathFeaturesForFunction(MethodContent $methods)
    {
        $functionLeaves = $methods->getLeaves();
        $programFeatures = new ProgramFeatures();

        for ($i = 0; $i < count($functionLeaves); $i++) {
            for ($j = $i + 1; $j < count($functionLeaves); $j++) {
                $source = $functionLeaves[$i];
                $target = $functionLeaves[$j];
                $path = $this->generatePath($source, $target);
                if ($path != $this->emptyString) {
                    $programFeatures->addFeature($source, $path, $target);
                }
            }
        }
        return $programFeatures;
    }

    /**
     * @return Node[]
     */
    public function getTreeStack(Node $node)
    {
        $upStack = [];
        $current = $node;
        while ($current != null) {
            $upStack[] = $current;
            $current = $current->getParent();
        }

        return $upStack;
    }

    public function generatePath(Node $source, Node $target)
    {
        $down = $this->downSymbol;
        $up = $this->upSymbol;
        $startSymbol = $this->lparen;
        $endSymbol = $this->rparen;

        $stringBuilder = [];
        $sourceStack = $this->getTreeStack($source);
        $targetStack = $this->getTreeStack($target);

        $commonPrefix = 0;
        $currentSourceAncestorIndex = count($sourceStack) - 1;
        $currentTargetAncestorIndex = count($targetStack) - 1;

        while ($currentSourceAncestorIndex >= 0 && $currentTargetAncestorIndex >= 0 && $sourceStack[$currentSourceAncestorIndex] === $targetStack[$currentTargetAncestorIndex]) {
            $commonPrefix++;
            $currentSourceAncestorIndex--;
            $currentTargetAncestorIndex--;
        }

        for ($i = 0; $i < count($sourceStack) - $commonPrefix; $i++) {
            $currentNode = $sourceStack[$i];
            $childId = $this->emptyString;
            $parentRawType = $currentNode->getParent();
            $nodeType = $currentNode->getNodeKindName();
            $result = $startSymbol . $nodeType . $childId . $endSymbol . $up;
            $stringBuilder[] = $result;
        }

        $commonNode = $sourceStack[count($sourceStack) - $commonPrefix];
        $commonNodeChildId = $this->emptyString;
        $parentNodeProperty = $commonNode->getParent();
        $commonNodeParentRawType = $this->emptyString;
        $commonResult = $startSymbol . $commonNode->getNodeKindName() . $endSymbol;
        $stringBuilder[] = $commonResult;

        for ($i = count($targetStack) - $commonPrefix - 1; $i >= 0; $i--) {
            $currentNode = $targetStack[$i];
            $nodeType = $currentNode->getNodeKindName();
            $childId = $this->emptyString;
            $result =  $down . $startSymbol . $nodeType . $childId . $endSymbol;
            $stringBuilder[] = $result;
        }
        return $stringBuilder;
    }
}
