<?php

namespace App\Service;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

class PhoneNumberService
{
    private PhoneNumberUtil $phoneUtil;

    public function __construct()
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    public function formatPhoneNumber(string $phone, string $country): ?string
    {
        try {
            // Parse le numéro en utilisant le pays par défaut
            $phoneNumber = $this->phoneUtil->parse($phone, $country);

            // Vérifie si c'est un numéro valide
            if (!$this->phoneUtil->isValidNumber($phoneNumber)) {
                return null; // Numéro invalide
            }

            // Retourne le numéro au format international (+33...)
            return $this->phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);

        } catch (NumberParseException $e) {
            return null; // Erreur de format
        }
    }
}
