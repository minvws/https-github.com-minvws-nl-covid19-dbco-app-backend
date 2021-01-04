<?php

namespace App\Models;

class ClassificationDetailsAnswer extends Answer
{
    public bool $category1Risk = false;
    public bool $category2ARisk = false;
    public bool $category2BRisk = false;
    public bool $category3Risk = false;

    public function isCompleted(): bool
    {
        return
            $this->category1Risk ||
            $this->category2ARisk ||
            $this->category2BRisk ||
            $this->category3Risk;
    }

    public function toFormValue(): ?string
    {
        // Beslisboom ggd
        if ($this->category1Risk) {
            return "1";
        }
        if ($this->category2ARisk) {
            return "2a";
        }
        if ($this->category2BRisk) {
            return "2b";
        }
        if ($this->category3Risk) {
            return "3";
        }

        return null;
    }
}
