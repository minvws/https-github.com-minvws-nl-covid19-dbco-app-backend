<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseMessage;

use App\Exceptions\MessageTemplateTypeException;
use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;

use function is_array;
use function is_string;

class SendMessageToIndexRequest extends ApiRequest
{
    private const FIELD_MESSAGE_TEMPLATE_TYPE = 'type';
    private const FIELD_ADDED_TEXT = 'addedText';
    private const FIELD_ATTACHMENTS = 'attachments';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            self::FIELD_MESSAGE_TEMPLATE_TYPE => [
                'required',
                Rule::in(MessageTemplateType::allValues()),
            ],
            self::FIELD_ADDED_TEXT => [
                'string',
            ],
        ];

        $rules[self::FIELD_ATTACHMENTS] = [
            'array',
        ];
        $rules[self::FIELD_ATTACHMENTS . '.*'] = [
            'uuid',
            'exists:attachment,uuid',
        ];

        return $rules;
    }

    /**
     * @throws MessageTemplateTypeException
     */
    public function getMessageTemplateType(): MessageTemplateType
    {
        $messageTemplateType = $this->input(self::FIELD_MESSAGE_TEMPLATE_TYPE);

        if (!is_string($messageTemplateType)) {
            throw new MessageTemplateTypeException('onbekend mail-type');
        }

        try {
            return MessageTemplateType::from($messageTemplateType);
        } catch (InvalidArgumentException) {
            throw new MessageTemplateTypeException('onbekend mail-type');
        }
    }

    public function getInputAddedText(): string
    {
        $input = $this->input(self::FIELD_ADDED_TEXT);

        if (!is_string($input)) {
            return '';
        }

        return $input;
    }

    /**
     * @return array<string>
     */
    public function getAttachments(): array
    {
        $attachments = $this->input(self::FIELD_ATTACHMENTS);

        if (!is_array($attachments)) {
            return [];
        }

        return $attachments;
    }
}
