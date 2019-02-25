<?php

namespace Mollie\Controllers;

use Mollie\Contracts\MethodSettingsRepositoryContract;
use Mollie\Services\MethodService;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

/**
 * Class SettingsController
 * @package Mollie\Controllers
 */
class SettingsController extends Controller
{
    /**
     * @param MethodService $methodService
     * @return array
     */
    public function getMethods(MethodService $methodService)
    {
        return $methodService->getMethodsForBackend();
    }

    /**
     * @param Request $request
     * @param MethodSettingsRepositoryContract $methodSettingsRepository
     * @param string $id
     * @return \Mollie\Models\MethodSetting
     * @throws \Plenty\Exceptions\ValidationException
     */
    public function saveMethodSetting(Request $request, MethodSettingsRepositoryContract $methodSettingsRepository, $id)
    {
        return $methodSettingsRepository->saveMethodSettings(
            array_merge(['id' => $id], $request->all())
        );
    }
}