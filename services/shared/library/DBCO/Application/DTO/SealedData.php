<?php

namespace DBCO\Shared\Application\DTO;

use DBCO\Shared\Application\Models\SealedData as SealedDataModel;
use JsonSerializable;

/**
 * Sealed data DTO.
 */
class SealedData implements JsonSerializable
{
    /**
     * @var SealedDataModel
     */
    public SealedDataModel $data;

    /**
     * Constructor.
     *
     * @param SealedDataModel $data
     */
    public function __construct(SealedDataModel $data)
    {
        $this->data = $data;
    }

    /**
     * Convert raw JSON decoded sealed data structure back to
     * object instance.
     *
     * @param array|stdClass $data
     *
     * @return SealedDataModel
     */
    public static function jsonUnserialize($data): SealedDataModel
    {
        if (is_array($data)) {
            return new SealedDataModel(
                base64_decode($data['ciphertext']),
                base64_decode($data['nonce'])
            );
        } else {
            return new SealedDataModel(
                base64_decode($data->ciphertext),
                base64_decode($data->nonce)
            );
        }
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange()]
    public function jsonSerialize()
    {
        return [
            'ciphertext' => base64_encode($this->data->ciphertext),
            'nonce' => base64_encode($this->data->nonce)
        ];
    }
}
