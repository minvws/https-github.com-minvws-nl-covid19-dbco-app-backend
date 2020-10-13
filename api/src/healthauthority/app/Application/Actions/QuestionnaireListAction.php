<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Helpers\TranslationHelper;
use App\Application\Responses\QuestionnaireListResponse;
use App\Application\Services\QuestionnaireService;
use DBCO\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * List questionnaires.
 *
 * @package App\Application\Actions
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
