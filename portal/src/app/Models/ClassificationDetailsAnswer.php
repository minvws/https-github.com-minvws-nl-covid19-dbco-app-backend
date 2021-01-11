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

    public static function fromFormValue(string $value): self
    {
        $answer = new self;

        switch ($value) {
            case '1':
                $answer->category1Risk = true;
                break;
            case '2a':
                $answer->category2ARisk = true;
                break;
            case '2b':
                $answer->category2BRisk = true;
                break;
            case '3':
                $answer->category3Risk = true;
                break;
        }

        return $answer;
    }

    public static function getValidationRules(): array
    {
        return [
            'value' => 'in:1,2a,2b,3'
        ];
    }
}
