<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Catalog;

use App\Schema\Purpose\PurposeDetail;
use App\Schema\Purpose\PurposeSpecification;
use MinVWS\Codable\EncodingContainer;

use function assert;

class PurposeSpecificationDecorator implements CatalogDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof PurposeSpecification);

        $container->purposes->encodeArray(
            $value->getAllPurposeDetails(),
            fn (EncodingContainer $c, PurposeDetail $d) => $this->encodePurposeDetail($d, $c)
        );
        $container->remark = $value->remark;
    }

    private function encodePurposeDetail(PurposeDetail $detail, EncodingContainer $container): void
    {
        $container->purpose->identifier = $detail->purpose->getIdentifier();
        $container->purpose->label = $detail->purpose->getLabel();
        $container->subPurpose->identifier = $detail->subPurpose->getIdentifier();
        $container->subPurpose->label = $detail->subPurpose->getLabel();
    }
}
