<?php
declare(strict_types=1);

use DBCO\Bridge\Application\Destinations\HttpDestination;
use DBCO\Bridge\Application\Sources\RedisSource;
use Psr\Http\Message\RequestInterface;
use function DI\autowire;
use function DI\get;
use function DI\value;

return [
    [
        'name' => 'clients',
        'description' => 'Process client pairing requests',
        'source' =>
            autowire(RedisSource::class)
                ->constructorParameter('key', 'clients'),
        'destination' =>
            autowire(HttpDestination::class)
                ->constructorParameter('client', get('healthAuthorityGuzzleClient'))
                ->constructorParameter('method', 'POST')
                ->constructorParameter('path', 'cases/{caseUuid}/clients')
                ->method('setRequestModifier', value(function (RequestInterface $request) {
                    // TODO: add authorization header
                    return $request->withHeader('Content-Type', 'application/json');
                }))
    ],
    [
        'name' => 'caseresults',
        'description' => 'Forward case results',
        'source' =>
            autowire(RedisSource::class)
                ->constructorParameter('key', 'caseresults'),
        'destination' =>
            autowire(HttpDestination::class)
                ->constructorParameter('client', get('healthAuthorityGuzzleClient'))
                ->constructorParameter('method', 'PUT')
                ->constructorParameter('path', 'cases/{caseToken}')
                ->method('setRequestModifier', value(function (RequestInterface $request) {
                    // TODO: add authorization header
                    return $request->withHeader('Content-Type', 'application/json');
                }))
    ]
];
