<?php

namespace App\FeatureEntities;

class ProgramFeatures
{
    private $features = [];

    public function addFeature($source, $path, $target)
    {
        $programRelation = new ProgramRelation($source, $target, $path);
        $this->features[] = $programRelation;
    }

    public function getFeatures()
    {
        return $this->features;
    }

    public function toString()
    {
        $func = function ($feature) {
            return $feature->toString();
        };
        return " ".implode(" ", array_map($func, $this->features));
    }
}
