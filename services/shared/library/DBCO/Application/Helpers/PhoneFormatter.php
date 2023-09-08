<?php

namespace DBCO\Shared\Application\Helpers;

use libphonenumber\NumberParseException;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneFormatter
{
    /**
     * Format the the phone number string to either dutch or international format.
     *
     * @param string $phoneNumberString
     * @return string
     */
    public static function format(string $phoneNumberString): string
    {
        //Try to match a Dutch phonenumber, otherwise it should be internatinal.
        try {
            $phoneNumber = new PhoneNumber($phoneNumberString, 'NL');
        } catch (NumberParseException $e) {
            $phoneNumber = new PhoneNumber($phoneNumberString);
        }

        try {
            if ($phoneNumber->getCountry() === 'NL') {
                return $phoneNumber->formatNational();
            }

            return $phoneNumber->formatInternational();
        } catch (NumberParseException $e) {
            //In case the it is not a valid number we just return the unformatted
            //number.
            return $phoneNumberString;
        }
    }
}
