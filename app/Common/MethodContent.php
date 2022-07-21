<?php

namespace App\Common;

class MethodContent
{
    private array $leaves;
    private string $name;
    private int $length;

    public function __construct($leaves)
    {
        $this->leaves = $leaves;
    }

    public function getLeaves()
    {
        return $this->leaves;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getLength()
    {
        return $this->length;
    }
}
