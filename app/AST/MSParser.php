<?php

namespace App\AST;

use App\FeatureEntities\ProgramFeatures;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node;
ini_set('memory_limit','-1');

class MSParser
{
    public $lparen = "(";
    public $rparen = ")";
    public $upSymbol = "^";
    public $downSymbol = "_";
    private $emptyString = "";
    /** @var Node[] */
    private $methods;
    /** @var Node[] */
    private $mainMethod;

    public function extractFeatures($code)
    {
        $methods = $this->parse($code);
        $methodLength = count($methods);
        foreach ($methods as $key => $method) {
            // echo count($method)."\n";
            if (count($method) >= 5000) {
                continue;
            }
            $result = $this->generatePathFeatures($method)->toString();
            if (strlen($result) <= 1) {
                continue;
            }
            // echo "=====================".$key."/".$methodLength."====================="."\n";
            // echo strlen($result)."\n";
            echo $result;
            if ($methodLength - 1 != $key) {
                echo "\n";
            }
        }
    }

    /**
     * @return Node[][]
     */
    public function parse($code)
    {
        $arr = [];
        $mainMethodLeave = [];
        $parser = new Parser();
        $astNode = $parser->parseSourceFile($code);

        $this->splitMethod($astNode);
        if ($this->methods) {
            foreach ($this->methods as $method) {
                $maxCodeLength = 10000;
                if (strlen($method->getText()) > $maxCodeLength) {
                    continue;
                }
                $arr[] = $this->getLeaves($method);
            }
        }
        if ($this->mainMethod) {
            foreach ($this->mainMethod as $nodes) {
                $leaves = $this->getLeaves($nodes);
                foreach ($leaves as $leave) {
                    $mainMethodLeave[] = $leave;
                }
            }
        }

        $arr[] = $mainMethodLeave;

        return $arr;
    }

    /**
     * @return Node[]
     */
    public function getLeaves(Node $node)
    {
        $arr = [];
        $node->getText();
        foreach ($node->getDescendantNodes() as $descendant) {
            $nodeType = $descendant->getNodeKindName();
            $childLength = count(iterator_to_array($descendant->getChildNodes(), false));
            if ($childLength === 0 && $nodeType != "exprInlineHtml" && $nodeType != "InlineHtml") {
                $arr[] = $descendant;
            }
        }
        return $arr;
    }

    public function splitMethod(Node $astNode)
    {
        foreach ($astNode->getChildNodes() as $childNode) {
            $nodeType = $childNode->getNodeKindName();
            if ($nodeType == "FunctionDeclaration") {
                $this->methods[] = $childNode;
            } elseif ($nodeType == "ClassDeclaration") {
                foreach ($childNode->getChildNodes() as $node) {
                    if ($node->getNodeKindName() == "ClassMembersNode") {
                        foreach ($node->getChildNodes() as $members) {
                            $this->methods[] = $members;
                        }
                    }
                }
            } else {
                $this->mainMethod[] = $childNode;
            }
        }
    }

    public function generatePathFeatures(array $methods)
    {
        $programFeatures = new ProgramFeatures();

        for ($i = 0; $i < count($methods); $i++) {
            for ($j = $i + 1; $j < count($methods); $j++) {
                $source = $methods[$i];
                $target = $methods[$j];
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

        $pathLength = count($sourceStack) + count($targetStack) - 2 * $commonPrefix;
        $maxPathLength = 8;
        if ($pathLength > $maxPathLength) {
            return $this->emptyString;
        }

        if ($currentSourceAncestorIndex >= 0 && $currentTargetAncestorIndex >= 0) {
            $maxPathWidth = 2;
            $pathWidth = $source->getWidth() - $target->getWidth();
            if ($pathWidth > $maxPathWidth) {
                return $this->emptyString;
            }
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
