<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ExpertQuestion;

use App\Http\Requests\Api\ApiRequest;

/**
 * @property string $assigned_user_uuid
 * @property string $answer
 */
class AnswerRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'answer' => [
                'required',
                'string',
                'max:5000',
            ],
        ];
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }
}
