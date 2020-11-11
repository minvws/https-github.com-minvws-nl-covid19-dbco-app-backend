<?php

namespace App\Models;

class ClassificationDetailsAnswer extends Answer
{
    public bool $category1Risk = false;
    public bool $category2ARisk = false;
    public bool $category2BRisk = false;
    public bool $category3Risk = false;

    function progressContribution()
    {
        return 0;
    }
}
