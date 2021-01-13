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

    public function fromFormValue(array $formData)
    {
        // Clear old classification
        $this->category1Risk = false;
        $this->category2ARisk = false;
        $this->category2BRisk = false;
        $this->category3Risk = false;

        switch ($formData['value']) {
            case '1':
                $this->category1Risk = true;
                break;
            case '2a':
                $this->category2ARisk = true;
                break;
            case '2b':
                $this->category2BRisk = true;
                break;
            case '3':
                $this->category3Risk = true;
                break;
        }
    }

    public static function createFromFormValue(string $value): self
    {
        $answer = new self;
        $answer->fromFormValue($value);
        return $answer;
    }

    public static function getValidationRules(): array
    {
        return [
            'value' => 'nullable|in:1,2a,2b,3'
        ];
    }
}
