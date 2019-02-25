<?php

namespace Mollie\Helpers;

use Plenty\Modules\System\Contracts\SystemInformationRepositoryContract;

/**
 * Class TrackingURLHelper
 * @package Mollie\Helpers
 */
class TrackingURLHelper
{
    /**
     * @var string
     */
    private $systemLanguage = '';

    /**
     * TrackingURLHelper constructor.
     * @param SystemInformationRepositoryContract $systemInformationRepository
     */
    public function __construct(SystemInformationRepositoryContract $systemInformationRepository)
    {
        $this->systemLanguage = $systemInformationRepository->loadValue('systemLang');
    }

    /**
     * @param $parcelServiceTrackingUrl
     * @param $trackingNumber
     * @param $zip
     * @param string $lang
     * @return mixed
     */
    public function generateURL($parcelServiceTrackingUrl, $trackingNumber, $zip, $lang = '')
    {

        if (!$lang) $lang = $this->systemLanguage;

        $parcelServiceTrackingUrl = str_replace('[PaketNr]',
            $trackingNumber,
            str_replace('[PLZ]',
                $zip,
                str_replace('[Lang]',
                    $lang,
                    $parcelServiceTrackingUrl)));

        return str_replace('$PaketNr',
            $trackingNumber,
            str_replace('$PLZ',
                $zip,
                str_replace('$Lang',
                    $lang,
                    $parcelServiceTrackingUrl)));
    }
}