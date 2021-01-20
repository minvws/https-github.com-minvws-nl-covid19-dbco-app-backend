<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\Shared\Application\Bridge\BridgeException;
use DBCO\Shared\Application\Bridge\Client as BridgeClient;
use DBCO\Shared\Application\Bridge\Request as BridgeRequest;
use DBCO\Shared\Application\Bridge\RequestException as BridgeRequestException;
use DBCO\Shared\Application\DTO\SealedData as SealedDataDTO;
use DBCO\Shared\Application\Models\SealedData;
use RuntimeException;

/**
 * Used for submitting case results to the health authority.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
class BridgeCaseSubmitRepository implements CaseSubmitRepository
{
    private const CASE_RESULT_LIST_KEY = 'caseresults';

    /**
     * @var BridgeClient
     */
    private BridgeClient $client;

    /**
     * Constructor.
     *
     * @param BridgeClient $client
     */
    public function __construct(BridgeClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function submitCase(string $token, SealedData $sealedCase): void
    {
        $requestData = [
            'sealedCase' => new SealedDataDTO($sealedCase)
        ];

        $request =
            BridgeRequest::create(self::CASE_RESULT_LIST_KEY)
                ->setParam('caseToken', $token)
                ->setData(json_encode($requestData));

        try {
            $this->client->request($request);
        } catch (BridgeRequestException $e) {
            $message = json_decode($e->getResponse()->getData())->message ?? $e->getMessage();
            throw new RuntimeException('Case submit failed: ' . $message, 0, $e);
        } catch (BridgeException $e) {
            throw new RuntimeException('Case submit failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
