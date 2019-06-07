<?php
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

$country = SdkRestApi::getParam('country');

$phoneUtil = PhoneNumberUtil::getInstance();
try {
    $phoneNumber = $phoneUtil->parse(SdkRestApi::getParam('phone'), $country);
    if ($phoneUtil->isValidNumber($phoneNumber)) {
        return ['phone' => $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164)];
    } else {
        return ['phone' => false];
    }
} catch (NumberParseException $e) {
    return ['phone' => false];
}