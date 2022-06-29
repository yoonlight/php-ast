<?php

namespace App\FeatureEntities;

class ProgramRelation
{
    private $m_source;
    private $m_target;
    private $m_path;

    public function __construct($sourceName, $targetName, $path)
    {
        $this->m_source = $sourceName;
        $this->m_target = $targetName;
        $this->m_path = $path;
    }

    public function toString()
    {
        return $this->m_source . "," . implode("", $this->m_path) . "," . $this->m_target;
    }
}
