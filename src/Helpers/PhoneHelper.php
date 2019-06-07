<?php

namespace Mollie\Helpers;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;

/**
 * Class PhoneHelper
 * @package Mollie\Helpers
 */
class PhoneHelper
{
    /**
     * @var LibraryCallContract
     */
    private $libraryCall;

    /**
     * PhoneHelper constructor.
     * @param LibraryCallContract $libraryCall
     */
    public function __construct(LibraryCallContract $libraryCall)
    {
        $this->libraryCall = $libraryCall;
    }

    /**
     * @param string $phone
     * @param string $countryCode
     * @return string|boolean
     */
    public function correctPhone($phone, $countryCode)
    {
        $result = $this->libraryCall->call(
            'Mollie::FormatPhone', [
                'country' => $countryCode,
                'phone'   => $phone
            ]
        );

        return $result['phone'];
    }
}