<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Actions;

use DBCO\PublicAPI\Application\Responses\QuestionnaireListResponse;
use DBCO\PublicAPI\Application\Services\QuestionnaireService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Helpers\TranslationHelper;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * List questionnaires.
 *
 * @package DBCO\PublicAPI\Application\Actions
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
     *
     * @throws Exception
     */
    protected function action(): Response
    {
        $acceptLanguage = $this->request->getHeader('Accept-Language');
        $language = $this->translationHelper->getLanguageForAcceptLanguageHeader($acceptLanguage);
        $data = $this->questionnaireService->getQuestionnaires($language);
        return $this->respond(new QuestionnaireListResponse($data));
    }
}
