<?php

namespace Mollie\Helpers;


use Plenty\Plugin\Log\Loggable;

/**
 * Class CeresHelper
 * @package Mollie\Helpers
 */
class CeresHelper
{
    use Loggable;

    /**
     * @param string $message
     */
    public function pushNotification($message)
    {
        try {
            $notificationService = pluginApp('\IO\Services\NotificationService');
            $notificationService->error($message);
        } catch (\Exception $exception) {
            $this->getLogger('CeresHelper')->logException($exception);
        }
    }
}