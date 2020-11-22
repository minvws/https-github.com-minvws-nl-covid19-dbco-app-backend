<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DateTime;
use DBCO\HealthAuthorityAPI\Application\Models\Answer as AnswerModel;
use DBCO\HealthAuthorityAPI\Application\Models\ClassificationDetails as ClassificationDetailsModel;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetails as ContactDetailsModel;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetailsFull as ContactDetailsFullModel;
use DBCO\HealthAuthorityAPI\Application\Models\Question as QuestionModel;
use DBCO\HealthAuthorityAPI\Application\Models\Questionnaire as QuestionnaireModel;
use DBCO\HealthAuthorityAPI\Application\Models\SimpleDateValue as SimpleDateValueModel;
use DBCO\HealthAuthorityAPI\Application\Models\SimpleStringValue as SimpleStringValueModel;
use DBCO\Shared\Application\Codable\DecodableDecorator;
use DBCO\Shared\Application\Codable\DecodePathException;
use DBCO\Shared\Application\Codable\DecodingContainer;

/**
 * Answer DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class Answer implements DecodableDecorator
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
     * @inheritDoc
     */
    public static function decode(string $class, DecodingContainer $container): object
    {
        $answer = new AnswerModel();
        $answer->uuid = $container->uuid->decodeString();
        $answer->lastModified = $container->lastModified->decodeDateTime(DateTime::ATOM);
        $answer->questionUuid = $container->questionUuid->decodeString();

        /** @var QuestionnaireModel $questionnaire */
        $questionnaire = $container->getContext()->getValue('questionnaire');

        /** @var QuestionModel|null $question */
        $question = null;
        foreach ($questionnaire->questions as $q) {
            if ($q->uuid == $answer->questionUuid) {
                $question = $q;
                break;
            }
        }

        if ($question === null) {
            $path = array_merge($container->getPath(), ['questionUuid']);
            throw new DecodePathException(
                $path,
                "Question UUID is invalid for path '" . DecodePathException::convertPathToString($path) . "'"
            );
        }

        switch ($question->questionType) {
            case 'open':
            case 'text':
            case 'multiplechoice':
                $answer->value = new SimpleStringValueModel();
                $answer->value->value = $container->value->value->decodeStringIfPresent();
                break;
            case 'classificationdetails':
                $answer->value = new ClassificationDetailsModel();
                $answer->value->category1Risk = $container->value->category1Risk->decodeBool();
                $answer->value->category2ARisk = $container->value->category2ARisk->decodeBool();
                $answer->value->category2BRisk = $container->value->category2BRisk->decodeBool();
                $answer->value->category3Risk = $container->value->category3Risk->decodeBool();
                break;
            case 'contactdetails':
                $answer->value = new ContactDetailsModel();
                $answer->value->firstName = $container->value->firstName->decodeStringIfPresent();
                $answer->value->lastName = $container->value->lastName->decodeStringIfPresent();
                $answer->value->phoneNumber = $container->value->phoneNumber->decodeStringIfPresent();
                $answer->value->email = $container->value->email->decodeStringIfPresent();
                break;
            case 'contactdetails_full':
                $answer->value = new ContactDetailsFullModel();
                $answer->value->firstName = $container->value->firstName->decodeStringIfPresent();
                $answer->value->lastName = $container->value->lastName->decodeStringIfPresent();
                $answer->value->phoneNumber = $container->value->phoneNumber->decodeStringIfPresent();
                $answer->value->email = $container->value->email->decodeStringIfPresent();
                $answer->value->address1 = $container->value->address1->decodeStringIfPresent();
                $answer->value->houseNumber = $container->value->houseNumber->decodeStringIfPresent();
                $answer->value->address2 = $container->value->address2->decodeStringIfPresent();
                $answer->value->zipcode = $container->value->zipcode->decodeStringIfPresent();
                $answer->value->city = $container->value->city->decodeStringIfPresent();
                break;
            case 'date':
                $answer->value = new SimpleDateValueModel();
                $answer->value->value = $container->value->value->decodeDateTimeIfPresent('Y-m-d');
                break;
            default:
                $path = array_merge($container->getPath(), ['questionUuid']);
                throw new DecodePathException(
                    $path,
                    "Unknown question type for path '" . DecodePathException::convertPathToString($path) . "'"
                );
        }

        return $answer;
    }
}
