<?php

namespace App\Models;

class ClassificationDetailsAnswer extends Answer
{
    public bool $livedTogetherRisk = false;
    public bool $durationRisk = false;
    public bool $distanceRisk = false;
    public bool $otherRisk = false;
}
