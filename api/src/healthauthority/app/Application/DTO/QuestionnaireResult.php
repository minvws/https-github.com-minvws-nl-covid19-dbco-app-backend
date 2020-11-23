<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\Answer as AnswerModel;
use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireResult as QuestionnaireResultModel;
use DBCO\Shared\Application\Codable\DecodableDecorator;
use DBCO\Shared\Application\Codable\DecodePathException;
use DBCO\Shared\Application\Codable\DecodingContainer;
use DBCO\Shared\Application\Codable\DecodingObject;

/**
 * QuestionnaireResult DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class QuestionnaireResult implements DecodableDecorator
{
    /**
     * @var QuestionnaireResultModel $questionnaireResult
     */
    private QuestionnaireResultModel $questionnaireResult;

    /**
     * Constructor.
     *
     * @param QuestionnaireResultModel $questionnaireResult
     */
    public function __construct(QuestionnaireResultModel $questionnaireResult)
    {
        $this->questionnaireResult = $questionnaireResult;
    }

    /**
     * @inheritDoc
     */
    public static function decode(string $class, DecodingContainer $container): object
    {
        $questionnaireResult = new QuestionnaireResultModel();
        $questionnaireResult->questionnaireUuid = strtolower($container->questionnaireUuid->decodeString());

        $questionnaires = $container->getContext()->getValue('questionnaires');
        $questionnaire = $questionnaires[$questionnaireResult->questionnaireUuid] ?? null;

        if ($questionnaire === null) {
            $path = array_merge($container->getPath(), ['questionnaireUuid']);
            throw new DecodePathException(
                $path,
                "Questionnaire UUID is invalid for path '" . DecodePathException::convertPathToString($path) . "'"
            );
        }

        $container->getContext()->setValue('questionnaire', $questionnaire);

        $questionnaireResult->answers =
            $container->answers->decodeArray(
                fn (DecodingContainer $c) => $c->decodeObject(AnswerModel::class)
            );

        return $questionnaireResult;
    }
}
