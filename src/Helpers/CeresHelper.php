<?php

namespace Mollie\Helpers;


/**
 * Class CeresHelper
 * @package Mollie\Helpers
 */
class CeresHelper
{
    /**
     * @param string $message
     */
    public function pushNotification($message)
    {
        try {
            $notificationService = pluginApp('\IO\Services\NotificationService');
            $notificationService->error($message);
        } catch (\Exception $exception) {
            
        }
    }
}