<?php

namespace App\Models;

class SimpleAnswer extends Answer
{
    public string $value;

    function progressContribution()
    {
        return 0;
    }
}
