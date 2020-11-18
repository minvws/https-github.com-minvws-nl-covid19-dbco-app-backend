<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DateTime;
use DBCO\HealthAuthorityAPI\Application\Models\Answer as AnswerModel;
use stdClass;

/**
 * Answer DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class Answer
{
    /**
     * @var AnswerModel $answer
     */
    private AnswerModel $answer;

    /**
     * Constructor.
     *
     * @param AnswerModel $answer
     */
    public function __construct(AnswerModel $answer)
    {
        $this->answer = $answer;
    }

    /**
     * Unserialize JSON data structure.
     *
     * @param stdClass $data
     *
     * @return AnswerModel
     */
    public static function jsonUnserialize(stdClass $data): AnswerModel
    {
        $answer = new AnswerModel();
        $answer->uuid = $data->uuid;
        $answer->lastModified = DateTime::createFromFormat(DateTime::ATOM, $data->lastModified);
        $answer->questionUuid = $data->questionUuid;

        // TODO: at this time we don't know the question, so we also don't know the answer type
        //       so we do some hardcoded checks to determine the answer type

        if (property_exists($data->value, 'value')) {
            $answer->value = SimpleValue::jsonUnserialize($data->value);
        } else if (property_exists($data->value, 'category1Risk')) {
            $answer->value = ClassificationDetails::jsonUnserialize($data->value);
        } else if (property_exists($data->value, 'address1')) {
            $answer->value = ContactDetailsFull::jsonUnserialize($data->value);
        } else if (property_exists($data->value, 'firstName')) {
            $answer->value = ContactDetails::jsonUnserialize($data->value);
        } else {
            // TODO: throw an error?
            $answer->value = new SimpleValue();
            $answer->value->value = '';
        }

        return $answer;
    }
}
