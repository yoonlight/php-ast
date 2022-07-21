<?php

namespace App\Visitors;

use App\Common\MethodContent;
use Microsoft\PhpParser\Node;

class FunctionVisitor
{
    private array $m_Methods = [];

    public function visit(Node $nodes)
    {
        foreach ($nodes->getDescendantNodes() as $node) {
            if ($node) {
                $leavesCollectorVisitor = new LeavesCollectorVisitor();
                $leavesCollectorVisitor->process($node);
                $leaves = $leavesCollectorVisitor->getLeaves();
                $this->m_Methods[] = new MethodContent($leaves);
            }
        }
    }

    /**
     * @return MethodContent[]
     */
    public function getMethodContents()
    {
        return $this->m_Methods;
    }
}
