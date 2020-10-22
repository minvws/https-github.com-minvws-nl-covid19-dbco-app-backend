<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Helpers\TranslationHelper;
use DBCO\HealthAuthorityAPI\Application\Responses\QuestionnaireListResponse;
use DBCO\HealthAuthorityAPI\Application\Services\QuestionnaireService;
use DBCO\Shared\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * List questionnaires.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Actions
 */
class QuestionnaireListAction extends Action
{
    /**
     * @var QuestionnaireService
     */
    private QuestionnaireService $questionnaireService;

    /**
     * @var TranslationHelper
     */
    private TranslationHelper $translationHelper;

    /**
     * Constructor.
     *
     * @param LoggerInterface      $logger
     * @param QuestionnaireService $questionnaireService
     * @param TranslationHelper    $translationHelper
     */
    public function __construct(
        LoggerInterface $logger,
        QuestionnaireService $questionnaireService,
        TranslationHelper $translationHelper
    )
    {
        parent::__construct($logger);
        $this->questionnaireService = $questionnaireService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $acceptLanguage = $this->request->getHeader('Accept-Language');
        $language = $this->translationHelper->getLanguageForAcceptLanguageHeader($acceptLanguage);
        $data = $this->questionnaireService->getQuestionnaires($language);
        return $this->respond(new QuestionnaireListResponse($data));
    }
}
