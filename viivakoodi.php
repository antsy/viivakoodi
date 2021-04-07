<?php

/**
 * Generates bar codes for bank transfers
 *
 * Absolutely no warranty!
 */
class BarCode
{
    private $iban;
    private $barcodeVersion;
    private $sepChar;

    /**
     * BarCode constructor.
     *
     * @param   string  $iban                 IBAN account number, without country identifier
     * @param   int     $version              Use 4 when your reference numbers are national, 5 when international
     * @param   string  $separatorCharacter   Set to ' ' to print out prettier numbers
     *
     * @throws  ErrorException              When validations fail
     */
    public function __construct($iban, $version = 5, $separatorCharacter = '') {
        if (strlen("{$iban}") != 16) {
            throw new ErrorException("Check account number, expected 16 digits, got '{$iban}'");
        }
        if (!($version == 4 || $version == 5)) {
            throw new ErrorException("Bad barcode version, only 4 and 5 are supported, {$version} was given.");
        }
        $this->iban = $iban;
        $this->barcodeVersion = $version;
        $this->sepChar = $separatorCharacter;
    }

    /**
     * @param   int     $euros              Money full part
     * @param   int     $cents              Money hundredth part
     * @param   string  $reference          Reference number
     * @param   string  $dueDate            Due date in YYMMDD format
     *
     * @return  string                      Generated barcode
     *
     * @throws ErrorException               When validations fail
     */
    public function getBarcode($euros = 0, $cents = 0, $reference = '', $dueDate = '000000') {
        if ($euros < 0 || $cents < 0) {
            throw new ErrorException("Money can't be negative");
        }
        if (strlen("{$euros}") > 6) {
            throw new ErrorException("Sum too large, maximum value is 999999");
        }
        if (strlen("{$cents}") > 2) {
            throw new ErrorException("Sum too large, cents cannot exceed 99");
        }
        $referenceNumber = $this->getReferenceNumber($reference);
        if (strlen("{$referenceNumber}") != 23) {
            throw new ErrorException("Reference number calculation error, expected 23 digits, got '{$referenceNumber}', check the reference number (${reference})!");
        }

        $barcode = "{$this->barcodeVersion}{$this->sepChar}{$this->iban}{$this->sepChar}";
        $barcode .= str_pad($euros, 6, '0', STR_PAD_LEFT);
        $barcode .= str_pad($cents, 2, '0', STR_PAD_LEFT);
        $barcode .= "{$this->sepChar}{$referenceNumber}{$this->sepChar}{$dueDate}";

        return $barcode;
    }

    /**
     * @param string $account       Regular account number
     *
     * @return string               Numeric IBAN account number (without country identifier)
     */
    public static function getNumericIBAN(string $account) {
        return preg_replace('/[^0-9]/', '', $account);
    }

    /**
     * Convert simple reference number to international RF format, but excluding the "RF" prefix.
     */
    private function getReferenceNumber($ref) {
        $ref = preg_replace('/\s+/', '', $ref);
        if ($this->barcodeVersion == 4) {
            $zeroPadding = str_repeat('0', 23 - strlen("{$ref}"));
            return "{$zeroPadding}{$ref}";
        } elseif ($this->barcodeVersion == 5) {
            if (substr($ref, 0, 2) === 'RF') {
                $ref = substr($ref, 4);
            }
            $checksum = (98 - bcmod($this->replaceCharacters("{$ref}RF00"), '97'));
            $checksum = str_pad($checksum, 2, '0', STR_PAD_LEFT);
            $zeroPadding = str_repeat('0', 21 - strlen("{$ref}"));
            return "{$checksum}{$zeroPadding}{$ref}";
        }
        return 'error';
    }

    /**
     * Some black magic to convert possible characters in reference number to integers
     */
    private function replaceCharacters($input)
    {
        return implode('', array_map(function($chr) {
            if (is_numeric($chr)) {
                return $chr;
            } else {
                $value = ord($chr) - 55;
                if ($value < 10 || $value > 35) {
                    throw new ErrorException("Value out of range, expected A-Z, got: ${$chr}");
                }
                return $value;
            }
        }, str_split($input)));
    }
}
