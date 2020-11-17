<?php
namespace DBCO\Shared\Application\Models;

/**
 * Encrypted/sealed message.
 */
class SealedData
{
    /**
     * @var string
     */
    public string $ciphertext;

    /**
     * @var string
     */
    public string $nonce;

    /**
     * Constructor.
     *
     * @param string $ciphertext
     * @param string $nonce
     */
    public function __construct(string $ciphertext, string $nonce)
    {
        $this->ciphertext = $ciphertext;
        $this->nonce = $nonce;
    }
}