<?php


namespace App\Services;

use Propaganistas\LaravelPhone\PhoneNumber;

class FormatPhoneNumber
{

    public function formatPhoneNumber(string $phonenumber): string
    {
        return PhoneNumber::make($phonenumber)->formatE164();
    }
}
