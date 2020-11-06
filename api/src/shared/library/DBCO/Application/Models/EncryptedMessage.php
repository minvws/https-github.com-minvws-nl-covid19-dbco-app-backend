<?php
/**
 * Encrypted message.
 */
class EncryptedMessage
{
    /**
     * @var
     */
    private string $ciphertext;

    /**
     * @var
     */
    private string $nonce;

    /**
     * Constructor.
     *
     * @param string $ciphertext
     * @param string $nonce
     */
    public function __construct(string $ciphertext, string $nonce)
    {
        $this->ciphertext = ciphertext;
        $this->nonce = $nonce;
    }
}