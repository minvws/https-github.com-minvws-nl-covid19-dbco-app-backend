<?php

namespace MinVWS\DBCO\PairingRequest\DI;

use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\ReflectionBasedAutowiring;
use MinVWS\DBCO\PairingRequest\Helpers\SecureCodeGenerator;
use MinVWS\DBCO\PairingRequest\Repositories\HealthAuthorityPairingRequestRepository;
use MinVWS\DBCO\PairingRequest\Repositories\IndexPairingRequestRepository;
use MinVWS\DBCO\PairingRequest\Repositories\RedisHealthAuthorityPairingRequestRepository;
use MinVWS\DBCO\PairingRequest\Repositories\RedisIndexPairingRequestRepository;
use MinVWS\DBCO\PairingRequest\Services\HealthAuthorityPairingRequestService;
use MinVWS\DBCO\PairingRequest\Services\IndexPairingRequestService;

use function DI\autowire;
use function DI\get;

/**
 * Container definitions.
 *
 * @package MinVWS\DBCO\PairingRequest\DI
 */
class Provider extends DefinitionArray
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct([], new ReflectionBasedAutowiring());

        // settings
        $this->addDefinitions([
            'indexPairingRequest.codeAllowedChars' => '1234567890',
            'indexPairingRequest.codeLength' => 6,
            'indexPairingRequest.expiresDelta' => 5 * 60, // 5 minutes
            'indexPairingRequest.completedExpiresAdditionalDelta' => 60, // after completion of the index pairing request,
                                                                         // we add an additional delta to the expires delta of
                                                                         // the index pairing request to make sure the client
                                                                         // receives the completion status in time
            'indexPairingRequest.expiredWarningDelta' => 15 * 60, // 15 minutes
            'indexPairingRequest.codeBlockedDelta' => 60 * 60, // 1 hour

            'healthAuthorityPairingRequest.codeAllowedChars' => '1234567890',
            'healthAuthorityPairingRequest.codeLength' => 12,
            'healthAuthorityPairingRequest.expiresDelta' => 45 * 60, // 45 minutes
            'healthAuthorityPairingRequest.expiredWarningDelta' => 24 * 60 * 60, // 1 day
            'healthAuthorityPairingRequest.codeBlockedDelta' => 30 * 24 * 60 * 60 // 30 days
        ]);

        // repositories
        $this->addDefinitions([
            IndexPairingRequestRepository::class => autowire(RedisIndexPairingRequestRepository::class),
            HealthAuthorityPairingRequestRepository::class => autowire(RedisHealthAuthorityPairingRequestRepository::class)
        ]);

        // services
        $this->addDefinitions([
            IndexPairingRequestService::class =>
                autowire(IndexPairingRequestService::class)
                    ->constructorParameter(
                        'codeGenerator',
                        autowire(SecureCodeGenerator::class)
                            ->constructorParameter('allowedChars', get('indexPairingRequest.codeAllowedChars'))
                            ->constructorParameter('length', get('indexPairingRequest.codeLength'))
                    )
                    ->constructorParameter('requestExpiresDelta', get('indexPairingRequest.expiresDelta'))
                    ->constructorParameter('requestExpiredWarningDelta', get('indexPairingRequest.expiredWarningDelta'))
                    ->constructorParameter('codeBlockedDelta', get('indexPairingRequest.codeBlockedDelta')),
            HealthAuthorityPairingRequestService::class =>
                autowire(HealthAuthorityPairingRequestService::class)
                    ->constructorParameter(
                        'codeGenerator',
                        autowire(SecureCodeGenerator::class)
                            ->constructorParameter('allowedChars', get('healthAuthorityPairingRequest.codeAllowedChars'))
                            ->constructorParameter('length', get('healthAuthorityPairingRequest.codeLength'))
                    )
                    ->constructorParameter('requestExpiresDelta', get('healthAuthorityPairingRequest.expiresDelta'))
                    ->constructorParameter('requestExpiredWarningDelta', get('healthAuthorityPairingRequest.expiredWarningDelta'))
                    ->constructorParameter('codeBlockedDelta', get('healthAuthorityPairingRequest.codeBlockedDelta'))
                    ->constructorParameter('indexPairingRequestExpiresAdditionalDelta', get('indexPairingRequest.completedExpiresAdditionalDelta'))
        ]);
    }
}
