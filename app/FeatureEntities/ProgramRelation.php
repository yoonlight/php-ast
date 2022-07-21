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
        $this->m_hashedPath = strval($this->hashCode(implode("", $path)));
    }

    public function normalizeName(string $token)
    {
        return trim(preg_replace('/[ \t]+/', '', preg_replace('/\s*$^\s*/m', "", preg_replace("/\r|\n/", "", strtolower($token)))));
    }

    public function toString()
    {
        return $this->normalizeName($this->m_source) . "," . $this->m_hashedPath . "," . $this->normalizeName($this->m_target);
    }

    private function overflow32($v)
    {
        $v = $v % 4294967296;
        if ($v > 2147483647) return $v - 4294967296;
        elseif ($v < -2147483648) return $v + 4294967296;
        else return $v;
    }

    private function hashCode($s)
    {
        $h = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $h = $this->overflow32(31 * $h + ord($s[$i]));
        }

        return $h;
    }
}
