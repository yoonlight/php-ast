<?php

namespace App\Visitors;

use Microsoft\PhpParser\Node;

class LeavesCollectorVisitor
{
    private $m_Leaves = [];

    public function process(Node $nodes)
    {

        foreach ($nodes->getDescendantNodes() as $node) {

            if ($this->hasNoChildren($node)) {
                $nodeType = $node->getNodeKindName();
                if ($this->isNotHtml($nodeType)) {
                    $this->m_Leaves[] = $node;
                }
            }
        }
    }


    private function hasNoChildren(Node $node)
    {
        return count(iterator_to_array($node->getChildNodes(), false)) === 0;
    }


    private function isNotHtml(string $nodeType)
    {
        return $nodeType != "exprInlineHtml" && $nodeType != "InlineHtml";
    }

    /**
     * @return Node[]
     */
    public function getLeaves()
    {
        return $this->m_Leaves;
    }
}
