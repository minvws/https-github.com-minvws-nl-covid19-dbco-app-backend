<?php
namespace App\Application\Responses;

/**
 * Questionnaire list response.
 */
class QuestionnaireListResponse extends ProxyResponse
{
    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        $headers = parent::getHeaders();
        $headers['Content-Type'] = 'application/json';
        return $headers;
    }
}
