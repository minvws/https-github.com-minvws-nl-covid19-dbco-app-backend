<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Actions;

use DBCO\PublicAPI\Application\Responses\ConfigResponse;
use DBCO\PublicAPI\Application\Services\ConfigService;
use DBCO\Shared\Application\Actions\Action;
use DBCO\Shared\Application\Helpers\TranslationHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Returns the app config.
 *
 * @package DBCO\PublicAPI\Application\Actions
 */
class ConfigAction extends Action
{
    /**
     * @var ConfigService
     */
    private ConfigService $configService;

    /**
     * @var TranslationHelper
     */
    private TranslationHelper $translationHelper;

    /**
     * Constructor.
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigService $configService,
        TranslationHelper $translationHelper
    )
    {
        parent::__construct($logger);
        $this->configService = $configService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $acceptLanguage = $this->request->getHeader('Accept-Language');
        $language = $this->translationHelper->getLanguageForAcceptLanguageHeader($acceptLanguage);
        $config = $this->configService->getConfig($language);
        return $this->respond(new ConfigResponse($config));
    }
}
