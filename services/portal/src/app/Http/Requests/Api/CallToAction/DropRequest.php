<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CallToAction;

use App\Http\Requests\Api\ApiRequest;

use function is_string;

/**
 * @property string $note
 */
class DropRequest extends ApiRequest
{
    public const FIELD_NOTE = 'note';

    public function rules(): array
    {
        return [
            self::FIELD_NOTE => 'required|string',
        ];
    }

    public function getNote(): string
    {
        $note = $this->get(self::FIELD_NOTE);

        return is_string($note) ? $note : '';
    }
}
