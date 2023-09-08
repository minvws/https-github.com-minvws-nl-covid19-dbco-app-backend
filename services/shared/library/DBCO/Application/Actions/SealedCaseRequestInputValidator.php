<?php

namespace DBCO\Shared\Application\Actions;

/**
 * Validate sealed case request input.
 *
 * @package DBCO\Shared\Application\Actions
 */
class SealedCaseRequestInputValidator
{
    public static function validate(array $sealedCase, array &$errors): void
    {
        if (empty($sealedCase)) {
            $errors[] = ValidationError::body('isRequired', 'sealedCase is required', ['sealedCase']);
        } else {
            if (empty($sealedCase['ciphertext'])) {
                $errors[] = ValidationError::body('isRequired', 'sealedCase.ciphertext is required', ['sealedCase', 'ciphertext']);
            }

            if (empty($sealedCase['nonce'])) {
                $errors[] = ValidationError::body('isRequired', 'sealedCase.nonce is required', ['sealedCase', 'nonce']);
            }
        }
    }
}
